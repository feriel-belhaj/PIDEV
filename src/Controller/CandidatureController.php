<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Form\CandidatureType;
use App\Repository\CandidatureRepository;
use App\Entity\Partenariat;
use App\Repository\PartenariatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    #[Route('/new/{id}', name: 'app_candidature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PartenariatRepository $partenariatRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $partenariat = $partenariatRepository->find($id);
        if (!$partenariat) {
            throw $this->createNotFoundException('Partenariat non trouvé.');
        }

        $candidature = new Candidature();
        $form = $this->createForm(CandidatureType::class, $candidature, [
            'is_edit' => false, // Mode ajout
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

            $candidature->setPartenariat($partenariat);
            $entityManager->persist($candidature);
            $entityManager->flush();

            $this->addFlash('success', 'Votre candidature a été enregistrée avec succès.');
            return $this->redirectToRoute('app_candidature_index');
        }

        return $this->render('candidature/new.html.twig', [
            'form' => $form->createView(),
            'partenariat' => $partenariat,
            'existingCv' => $candidature->getCv(),
            'existingPortfolio' => $candidature->getPortfolio(),
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_show', methods: ['GET'])]
    public function show(Candidature $candidature): Response
    {
        $uploadsDir = $this->getParameter('uploads_directory');
        $cvPath = $uploadsDir . '/' . $candidature->getCv();
        $cvExists = file_exists($cvPath) && is_file($cvPath);

        return $this->render('candidature/show.html.twig', [
            'candidature' => $candidature,
            'cvExists' => $cvExists,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_candidature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CandidatureType::class, $candidature, [
            'is_edit' => true, // Mode édition
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
                    return $this->redirectToRoute('app_candidature_edit', ['id' => $candidature->getId()]);
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
                    return $this->redirectToRoute('app_candidature_edit', ['id' => $candidature->getId()]);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre candidature a été mise à jour avec succès.');
            return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidature/edit.html.twig', [
            'candidature' => $candidature,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_candidature_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $candidature->getId(), $request->request->get('_token'))) {
            $filesystem = new Filesystem();
            $uploadsDir = $this->getParameter('uploads_directory');

            // Suppression du CV
            if ($candidature->getCv()) {
                $cvPath = $uploadsDir . '/' . $candidature->getCv();
                if ($filesystem->exists($cvPath)) {
                    $filesystem->remove($cvPath);
                }
            }

            // Suppression du portfolio
            if ($candidature->getPortfolio()) {
                $portfolioPath = $uploadsDir . '/' . $candidature->getPortfolio();
                if ($filesystem->exists($portfolioPath)) {
                    $filesystem->remove($portfolioPath);
                }
            }

            $entityManager->remove($candidature);
            $entityManager->flush();

            $this->addFlash('success', 'Candidature supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_candidature_index');
    }
}