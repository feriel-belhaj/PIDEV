<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use App\Entity\FormationReservee;
use App\GeoLocationBundle\Service\GeoCoderService;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/formation')]
final class FormationController extends AbstractController
{
    private $imagesDirectory;
    private $geoCoderService;

    public function __construct(
        ParameterBagInterface $params, 
        GeoCoderService $geoCoderService
    )
    {
        // Fetch the image directory parameter
        $this->imagesDirectory = $params->get('formation_images_directory');

        // Créer le dossier d'upload s'il n'existe pas
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->imagesDirectory)) {
            $filesystem->mkdir($this->imagesDirectory);
        }

        $this->geoCoderService = $geoCoderService;
    }




    #[Route('/', name: 'app_formation_index', methods: ['GET'])]
    public function index(FormationRepository $formationRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search');
        $niveau = $request->query->get('niveau');

        $queryBuilder = $formationRepository->createQueryBuilder('f');

        if ($search) {
            $queryBuilder->andWhere('f.titre LIKE :search OR f.description LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        if ($niveau) {
            $queryBuilder->andWhere('f.niveau = :niveau')
                         ->setParameter('niveau', $niveau);
        }

        $pagination = $paginator->paginate(
            $queryBuilder, // Query NOT getQuery()
            $request->query->getInt('page', 1), // Page number
            6 // 6 formations par page
        );

        return $this->render('formation/index.html.twig', [
            'formations' => $pagination,
        ]);
    }
   
    #[Route('/formations', name: 'app_formation_index1', methods: ['GET'])]
    public function index1(FormationRepository $formationRepository, Request $request): Response
    {
        $search = $request->query->get('search');
        $niveau = $request->query->get('niveau');

        $queryBuilder = $formationRepository->createQueryBuilder('f');

        if ($search) {
            $queryBuilder->andWhere('f.titre LIKE :search OR f.description LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        if ($niveau) {
            $queryBuilder->andWhere('f.niveau = :niveau')
                         ->setParameter('niveau', $niveau);
        }

        $formations = $queryBuilder->getQuery()->getResult();

        // Calcul des statistiques
        $totalFormations = count($formations);
        $totalReservees = 0;

        // Initialisation des compteurs pour chaque niveau
        $totalDebutant = 0;
        $totalIntermediaire = 0;
        $totalAvance = 0;

        foreach ($formations as $formation) {
            $totalReservees += $formation->getFormationsReservees()->count(); // Assurez-vous que cette méthode existe

            // Compter les formations par niveau
            if ($formation->getNiveau() === 'debutant') {
                $totalDebutant++;
            } elseif ($formation->getNiveau() === 'intermediaire') {
                $totalIntermediaire++;
            } elseif ($formation->getNiveau() === 'avance') {
                $totalAvance++;
            }
        }

        return $this->render('formation/index1.html.twig', [
            'formations' => $formations,
            'totalFormations' => $totalFormations,
            'totalReservees' => $totalReservees,
            'totalDebutant' => $totalDebutant,
            'totalIntermediaire' => $totalIntermediaire,
            'totalAvance' => $totalAvance,
        ]);
    }

    #[Route('/new', name: 'app_formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('formation_images_directory'),
                        $newFilename
                    );
                    $formation->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                }
            }

            // Gestion du fichier 3D
            $model3dFile = $form->get('model3dFile')->getData();
            if ($model3dFile) {
                // Debug - Vérifier que le fichier est bien reçu
                dump($model3dFile);
                $originalFilename = pathinfo($model3dFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$model3dFile->guessExtension();

                try {
                    $model3dFile->move(
                        $this->getParameter('models_directory'),
                        $newFilename
                    );
                    
                    // Mettre à jour l'URL du modèle 3D dans l'entité avec le chemin complet
                    $formation->setModel3dUrl('/uploads/models/' . $newFilename);
                    
                    // Debug - Vérifier l'état complet de l'entité
                    dump([
                        'formation_id' => $formation->getId(),
                        'model3d_url' => $formation->getModel3dUrl(),
                        'newFilename' => $newFilename,
                        'full_path' => $this->getParameter('models_directory') . '/' . $newFilename
                    ]);

                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du modèle 3D : ' . $e->getMessage());
                    // Debug - Log l'erreur
                    dump($e->getMessage());
                }
            }

            // Debug - Vérifier l'état de l'entité avant la sauvegarde
            dump([
                'model3dUrl' => $formation->getModel3dUrl(),
                'file_exists' => file_exists($this->getParameter('models_directory') . '/' . $newFilename)
            ]);

            $entityManager->persist($formation);
            // Debug - Vérifier l'état juste avant le flush
            dump($formation);
            $entityManager->flush();

            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        // Utiliser le service de géocodage
        $location = $this->geoCoderService->geocodeAddress($formation->getEmplacement());

        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
            'location' => $location,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_formation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                $oldImage = $formation->getImage();
                if ($oldImage) {
                    $oldImagePath = $this->getParameter('formation_images_directory').'/'.$oldImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('formation_images_directory'),
                        $newFilename
                    );
                    $formation->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                }
            }
            // Si pas de nouvelle image, on garde l'ancienne

            // Gestion du fichier 3D
            $model3dFile = $form->get('model3dFile')->getData();
            if ($model3dFile) {
                $originalFilename = pathinfo($model3dFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$model3dFile->guessExtension();

                try {
                    $model3dFile->move(
                        $this->getParameter('models_directory'),
                        $newFilename
                    );
                    
                    // Supprimer l'ancien fichier si il existe
                    $oldFile = $formation->getModel3dUrl();
                    if ($oldFile) {
                        $oldFilePath = $this->getParameter('kernel.project_dir').'/public'.$oldFile;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    // Mettre à jour l'URL du modèle 3D
                    $formation->setModel3dUrl('/uploads/models/'.$newFilename);
                } catch (FileException $e) {
                    // ... gérer l'exception
                }
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_formation_index1', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/edit.html.twig', [
            'formation' => $formation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formation->getId(), $request->request->get('_token'))) {
            try {
                // La suppression en cascade s'occupera des formationsReservees
                $entityManager->remove($formation);
                $entityManager->flush();
                
                $this->addFlash('success', 'La formation a été supprimée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la formation.');
            }
        }

        return $this->redirectToRoute('app_formation_index1', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/formation/{id}/add-to-cart', name: 'app_formation_add_to_cart', methods: ['POST'])]
    public function addToCart(
        int $id, 
        Request $request, 
        CsrfTokenManagerInterface $csrfTokenManager, 
        SessionInterface $session
    ) {
        // Vérifier le token CSRF
        $token = $request->request->get('_csrf_token');
    
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('add_to_cart', $token))) {
            throw new AccessDeniedException('Invalid CSRF token');
        }
    
        // Ajouter au panier
        $cart = $session->get('cart', []);
        $cart[] = $id;
        $session->set('cart', $cart);
    
        return $this->redirectToRoute('app_formation_index');
    }
    #[Route('/cart', name: 'app_formation_cart', methods: ['GET'])]

    public function showCart(Request $request, FormationRepository $formationRepository): Response
    {
        // Récupérer la session
        $session = $request->getSession();

        // Récupérer le panier depuis la session
        $cart = $session->get('cart', []);

        // Récupérer les formations correspondantes aux IDs du panier
        $formations = $formationRepository->findBy(['id' => $cart]);

        // Afficher la page du panier
        return $this->render('formation/cart.html.twig', [
            'formations' => $formations,
        ]);
    }
    
    #[Route('/formation/cart/{id}', name: 'app_cart')]
    public function cart(Formation $formation): Response
    {
        return $this->render('formation/cart.html.twig', [
            'formation' => $formation
        ]);
    }

    #[Route('/formation/payment', name: 'app_payment')]
    public function payment(): Response
    {
        // Cette méthode sera à implémenter plus tard pour le traitement du paiement
        return $this->render('formation/payment.html.twig');
    }

    #[Route('/formations/search', name: 'app_formation_search', methods: ['GET'])]
    public function searchFormations(Request $request, FormationRepository $formationRepository): JsonResponse
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'desc'); // 'desc' par défaut
        
        $queryBuilder = $formationRepository->createQueryBuilder('f');
        
        if ($search) {
            $queryBuilder->andWhere('f.titre LIKE :search OR f.description LIKE :search')
                        ->setParameter('search', '%' . $search . '%');
        }
        
        // Ajout du tri par date
        $queryBuilder->orderBy('f.datedeb', $sort);
        
        $formations = $queryBuilder->getQuery()->getResult();
        
        $results = [];
        foreach ($formations as $formation) {
            $results[] = [
                'id' => $formation->getId(),
                'titre' => $formation->getTitre(),
                'datedeb' => $formation->getDatedeb()->format('d/m/Y'),
                'datefin' => $formation->getDatefin()->format('d/m/Y'),
                'niveau' => $formation->getNiveau(),
                'prix' => $formation->getPrix(),
                'nbplace' => $formation->getNbplace(),
                'nbparticipant' => $formation->getNbparticipant()
            ];
        }
        
        return new JsonResponse($results);
    }
}
