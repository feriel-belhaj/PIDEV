<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Service\DandelionService;
use App\Service\ImageAnalysisService;
use App\Entity\Candidature;
use App\Entity\Utilisateur;
use App\Form\CandidatureType;
use App\Repository\CandidatureRepository;
use App\Repository\PartenariatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/candidature')]
final class CandidatureController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_candidature_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        PartenariatRepository $partenariatRepository, 
        EntityManagerInterface $entityManager, 
        DandelionService $dandelionService,
        ImageAnalysisService $imageAnalysisService,
        int $id
        ,Security $security
    ): Response {
        $partenariat = $partenariatRepository->find($id);
        if (!$partenariat) {
            throw $this->createNotFoundException('Partenariat non trouvé.');
        }

        $candidature = new Candidature();
        $form = $this->createForm(CandidatureType::class, $candidature, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $motsInterdits = ['insulte', 'violence', 'racisme', 'discrimination', 'fake news'];
            $motivationText = strtolower($candidature->getMotivation());
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';

            foreach ($motsInterdits as $mot) {
                if (str_contains($motivationText, strtolower($mot))) {
                    $this->addFlash('error', 'Votre lettre de motivation contient des mots interdits.');
                    return $this->redirectToRoute('app_candidature_new', ['id' => $id]);
                }
            }

            try {
                $textAnalysisResult = $dandelionService->analyzeText($motivationText);
                $entities = $textAnalysisResult['annotations'] ?? [];
                $candidature->setAnalysisResult(['entities' => $entities]);
            } catch (\Exception $e) {
                error_log("Erreur analyse texte: " . $e->getMessage());
                $this->addFlash('error', 'Erreur lors de l\'analyse du texte.');
            }

            // Gestion du fichier CV
            $cvFile = $form->get('cv')->getData();
            if ($cvFile) {
                $cvFilename = uniqid() . '.' . $cvFile->guessExtension();
                try {
                    $cvFile->move($uploadsDir, $cvFilename);
                    $candidature->setCv($cvFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du CV.');
                    return $this->redirectToRoute('app_candidature_new', ['id' => $id]);
                }
            }

            // Gestion du fichier Portfolio + Analyse d'image
            $portfolioFile = $form->get('portfolio')->getData();
            if ($portfolioFile) {
                $portfolioFilename = uniqid() . '.' . $portfolioFile->guessExtension();
                try {
                    $portfolioFile->move($uploadsDir, $portfolioFilename);
                    $candidature->setPortfolio($portfolioFilename);

                    // Vérification et analyse de l'image
                    $imagePath = $uploadsDir . $portfolioFilename;
                    if (is_readable($imagePath)) {
                        try {
                            $analysisResult = $imageAnalysisService->analyzeImage($imagePath);
                            dump($analysisResult); // Vérifie le retour de l'API dans la debug bar

                            if (isset($analysisResult['score'])) {
                                $candidature->setScoreArtistique($analysisResult['score']);
                            } else {
                                $candidature->setScoreArtistique(0);
                            }
                        } catch (\Exception $e) {
                            $this->addFlash('error', 'Erreur lors de l\'analyse de l\'image.');
                        }
                    } else {
                        $this->addFlash('error', 'Le fichier portfolio ne peut pas être lu.');
                    }
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du Portfolio.');
                    return $this->redirectToRoute('app_candidature_new', ['id' => $id]);
                }
            }
            $utilisateur = $security->getUser();
            if ( $utilisateur instanceof Utilisateur) {
                $candidature->setCreateur($utilisateur);
                
            }
            $candidature->setPartenariat($partenariat);
            $entityManager->persist($candidature);
            $entityManager->flush();

            $this->addFlash('success', 'Votre candidature a été enregistrée avec succès.');
            return $this->redirectToRoute('app_partenariat_index');
        }

        return $this->render('candidature/new.html.twig', [
            'form' => $form->createView(),
            'partenariat' => $partenariat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_candidature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CandidatureType::class, $candidature, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre candidature a été mise à jour avec succès.');
            return $this->redirectToRoute('admin_candidature_index');
        }

        return $this->render('candidature/edit.html.twig', [
            'candidature' => $candidature,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_show', methods: ['GET'])]
    public function show(Candidature $candidature): Response
    {
        return $this->render('candidature/show.html.twig', [
            'candidature' => $candidature,
            'cvExists' => !empty($candidature->getCv()),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_candidature_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $candidature->getId(), $request->request->get('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();
            $this->addFlash('success', 'Candidature supprimée avec succès.');
        }
        return $this->redirectToRoute('admin_candidature_index');
    }

    #[Route('/admin/candidatures', name: 'admin_candidature_index')]
    public function index(

    CandidatureRepository $candidatureRepository, 
    PaginatorInterface $paginator, 
    RequestStack $requestStack
): Response {
    $queryBuilder = $candidatureRepository->findAllOrderedByIdDescQuery();
    $currentPage = $requestStack->getCurrentRequest()->query->getInt('page', 1);
    $pagination = $paginator->paginate($queryBuilder, $currentPage, 6);

    $typeCollabStats = $candidatureRepository->getTypeCollabStats();

        // Récupération des résultats d'analyse
        $analysisResults = [];
        foreach ($pagination->getItems() as $candidature) {
            $decodedResult = $candidature->getAnalysisResultDecoded();
            if (isset($decodedResult['entities']) && is_array($decodedResult['entities'])) {
                foreach ($decodedResult['entities'] as &$entity) {
                    if (!isset($entity['categories'])) {
                        $entity['categories'] = []; // Assurer que 'categories' existe
                    }
                }
            }
            $analysisResults[$candidature->getId()] = $decodedResult;
        }

          // Statistiques des types de collaboration
          $typeCollabStats = $candidatureRepository->createQueryBuilder('c')
          ->select('c.typeCollab, COUNT(c.id) as type_count')
          ->groupBy('c.typeCollab')
          ->getQuery()
          ->getResult();

      return $this->render('candidature/admin_candidature_index.html.twig', [
          'pagination' => $pagination,
          'typeCollabStats' => $typeCollabStats,
      
            'analysisResults' => $analysisResults, // Envoi des résultats d'analyse au template
        ]);
    }
    
    
  
    
    #[Route('/uploads/{filename}', name: 'app_file_serve', methods: ['GET'])]
    public function serveFile(string $filename): Response
    {
        $filePath = $this->getParameter('uploads_directory') . '/' . $filename;
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier n\'a pas été trouvé');
        }
        return new Response(file_get_contents($filePath), 200, [
            'Content-Type' => mime_content_type($filePath),
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        ]);
    }

    #[Route('/candidature/pdf/{id}', name: 'app_candidature_pdf')]
    public function generatePdf(Candidature $candidature): Response
    {
        $analysisResults = $candidature->getAnalysisResultDecoded();

        // Rendu du fichier Twig en HTML
        $html = $this->renderView('candidature/pdf_template.html.twig', [
            'candidature' => $candidature,
            'analysis' => $analysisResults
        ]);

        // Configuration de Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retourne le PDF en réponse HTTP
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="analyse_candidature.pdf"'
        ]);
    }

    private function getAnalysisResults(int $id)
    {
        // Récupérer les résultats d'analyse (simulé ici)
        return [
            'entities' => [
                ['label' => 'Exemple', 'confidence' => 0.95, 'categories' => ['Catégorie 1']],
            ],
            'themes' => [
                ['label' => 'Thème Exemple'],
            ],
        ];
    }
}