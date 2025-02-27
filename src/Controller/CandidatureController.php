<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Form\CandidatureType;
use App\Repository\CandidatureRepository;
<<<<<<< Updated upstream
=======
use App\Entity\Partenariat;
use App\Entity\Utilisateur;
use App\Repository\PartenariatRepository;
>>>>>>> Stashed changes
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/candidature')]
final class CandidatureController extends AbstractController
{
    #[Route(name: 'app_candidature_index', methods: ['GET'])]
    public function index(CandidatureRepository $candidatureRepository): Response
    {
        return $this->render('candidature/index.html.twig', [
            'candidatures' => $candidatureRepository->findAll(),
        ]);
    }

<<<<<<< Updated upstream
    #[Route('/new', name: 'app_candidature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
=======
    #[Route('/new/{id}', name: 'app_candidature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PartenariatRepository $partenariatRepository, EntityManagerInterface $entityManager, int $id, Security $security): Response
>>>>>>> Stashed changes
    {
        $candidature = new Candidature();
        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
<<<<<<< Updated upstream
=======
            $candidature->setDatePostulation(new \DateTime());

            $uploadsDir = $this->getParameter('uploads_directory');

            // Gestion du CV
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

            // Gestion du Portfolio
            $portfolioFile = $form->get('portfolio')->getData();
            if ($portfolioFile) {
                $portfolioFilename = uniqid() . '.' . $portfolioFile->guessExtension();
                try {
                    $portfolioFile->move($uploadsDir, $portfolioFilename);
                    $candidature->setPortfolio($portfolioFilename);
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
>>>>>>> Stashed changes
            $entityManager->persist($candidature);
            $entityManager->flush();

            return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidature/new.html.twig', [
            'candidature' => $candidature,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_show', methods: ['GET'])]
    public function show(Candidature $candidature): Response
    {
        return $this->render('candidature/show.html.twig', [
            'candidature' => $candidature,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_candidature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidature/edit.html.twig', [
            'candidature' => $candidature,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$candidature->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
    }
}
