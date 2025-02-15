<?php

namespace App\Controller;

use App\Entity\Partenariat;
use App\Form\PartenariatType;
use App\Repository\PartenariatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/partenariat')]
final class PartenariatController extends AbstractController
{
    #[Route('/', name: 'app_partenariat_index', methods: ['GET'])]
    public function index(PartenariatRepository $partenariatRepository): Response
    {
        return $this->render('partenariat/index.html.twig', [
            'partenariats' => $partenariatRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_partenariat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $partenariat = new Partenariat();
        $form = $this->createForm(PartenariatType::class, $partenariat, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('partenariat_images_directory'), $newFilename);
                $partenariat->setImage($newFilename);
            }

            $entityManager->persist($partenariat);
            $entityManager->flush();

            $this->addFlash('success', 'Partenariat ajouté avec succès.');
            return $this->redirectToRoute('app_partenariat_index');
        }

        return $this->render('partenariat/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_partenariat_show', methods: ['GET'])]
    public function show(Partenariat $partenariat): Response
    {
        return $this->render('partenariat/show.html.twig', [
            'partenariat' => $partenariat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_partenariat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Partenariat $partenariat, EntityManagerInterface $entityManager): Response
    {
        $originalImage = $partenariat->getImage();
        $form = $this->createForm(PartenariatType::class, $partenariat, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('partenariat_images_directory'), $newFilename);
                $partenariat->setImage($newFilename);
            } else {
                $partenariat->setImage($originalImage);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Partenariat mis à jour avec succès.');

            return $this->redirectToRoute('app_partenariat_index');
        }

        return $this->render('partenariat/edit.html.twig', [
            'partenariat' => $partenariat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_partenariat_delete', methods: ['POST'])]
    public function delete(Request $request, Partenariat $partenariat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partenariat->getId(), $request->request->get('_token'))) {
            // Supprimer les candidatures associées
            foreach ($partenariat->getCandidatures() as $candidature) {
                $entityManager->remove($candidature);
            }

            // Supprimer le partenariat
            $entityManager->remove($partenariat);
            $entityManager->flush();

            $this->addFlash('success', 'Partenariat supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_partenariat_index');
    }
}
