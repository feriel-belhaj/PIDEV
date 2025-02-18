<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commentaire')]
class CommentaireController extends AbstractController
{
    private $entityManager;
    private $commentaireRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommentaireRepository $commentaireRepository
    ) {
        $this->entityManager = $entityManager;
        $this->commentaireRepository = $commentaireRepository;
    }

    #[Route('/', name: 'commentaire_list')]
    public function index(): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'tabservice' => $this->commentaireRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'commentaire_new')]
    public function new(Request $request): Response
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($commentaire);
            $this->entityManager->flush();
            return $this->redirectToRoute('commentaire_list');
        }

        return $this->render('commentaire/addForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'commentaire_edit')]
    public function edit(Request $request, int $id): Response
    {
        $commentaire = $this->commentaireRepository->find($id);
        
        if (!$commentaire) {
            throw $this->createNotFoundException('Commentaire non trouvé');
        }

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('commentaire_list');
        }

        return $this->render('commentaire/modForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'commentaire_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $commentaire = $this->commentaireRepository->find($id);
        
        if (!$commentaire) {
            throw $this->createNotFoundException('Commentaire non trouvé');
        }

        $this->entityManager->remove($commentaire);
        $this->entityManager->flush();

        return $this->redirectToRoute('commentaire_list');
    }
}
