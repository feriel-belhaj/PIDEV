<?php

namespace App\Controller;

use App\Entity\Partenariat;
use App\Entity\Utilisateur;
use App\Form\PartenariatType;
use App\Repository\PartenariatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/partenariat')]
final class PartenariatController extends AbstractController
{
    #[Route(name: 'app_partenariat_index', methods: ['GET'])]
    public function index(PartenariatRepository $partenariatRepository): Response
    {
        return $this->render('partenariat/index.html.twig', [
            'partenariats' => $partenariatRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_partenariat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $partenariat = new Partenariat();
        $form = $this->createForm(PartenariatType::class, $partenariat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
<<<<<<< Updated upstream
=======
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('partenariat_images_directory'), $newFilename);
                $partenariat->setImage($newFilename);
            }

            
            $utilisateur = $security->getUser(); // Récupérer l'utilisateur connecté

            if ( $utilisateur instanceof Utilisateur) {
                $partenariat->setCreateur($utilisateur);
                $partenariat->addUtilisateur($utilisateur);
            }

>>>>>>> Stashed changes
            $entityManager->persist($partenariat);
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $this->redirectToRoute('app_partenariat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partenariat/new.html.twig', [
            'partenariat' => $partenariat,
            'form' => $form,
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
        $form = $this->createForm(PartenariatType::class, $partenariat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_partenariat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partenariat/edit.html.twig', [
            'partenariat' => $partenariat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_partenariat_delete', methods: ['POST'])]
    public function delete(Request $request, Partenariat $partenariat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partenariat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($partenariat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_partenariat_index', [], Response::HTTP_SEE_OTHER);
    }
}
