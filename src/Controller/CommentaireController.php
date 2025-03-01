<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Creation;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use App\Service\EmailService;
use App\Service\ProfanityFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commentaire')]
class CommentaireController extends AbstractController
{
    private $entityManager;
    private $commentaireRepository;
    private $profanityFilter;
    private $emailService;
    private $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommentaireRepository $commentaireRepository,
        ProfanityFilterService $profanityFilter,
        EmailService $emailService,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->commentaireRepository = $commentaireRepository;
        $this->profanityFilter = $profanityFilter;
        $this->emailService = $emailService;
        $this->security = $security;
    }

    #[Route('/', name: 'commentaire_list')]
    public function index(): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'tabservice' => $this->commentaireRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'commentaire_new')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request): Response
    {
        $commentaire = new Commentaire();
        
        // Get the current authenticated user
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un commentaire.');
            return $this->redirectToRoute('app_login');
        }
        
        // Set the current user as the comment author
        $commentaire->setUtilisateur($currentUser);
        
        $form = $this->createForm(CommentaireType::class, $commentaire);
        // Remove the utilisateur field since we're setting it automatically
        $form->remove('utilisateur');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Filter profanity from comment content
            $filteredContent = $this->profanityFilter->filterText($commentaire->getContenu());
            $commentaire->setContenu($filteredContent);
            
            $this->entityManager->persist($commentaire);
            $this->entityManager->flush();
            
            // Send email notifications
            $this->sendCommentNotifications($commentaire);
            
            $this->addFlash('success', 'Commentaire ajouté avec succès');
            return $this->redirectToRoute('commentaire_list');
        }

        return $this->render('commentaire/addForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'commentaire_edit')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request, int $id): Response
    {
        $commentaire = $this->commentaireRepository->find($id);
        
        if (!$commentaire) {
            throw $this->createNotFoundException('Commentaire non trouvé');
        }
        
        // Check if the current user is the owner of the comment
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            $this->addFlash('error', 'Vous devez être connecté pour modifier un commentaire.');
            return $this->redirectToRoute('app_login');
        }
        
        if ($commentaire->getUtilisateur() !== $currentUser && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier ce commentaire.');
            return $this->redirectToRoute('commentaire_list');
        }

        $form = $this->createForm(CommentaireType::class, $commentaire);
        // Remove the utilisateur field since we're maintaining the original author
        $form->remove('utilisateur');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Filter profanity from comment content during edit
            $filteredContent = $this->profanityFilter->filterText($commentaire->getContenu());
            $commentaire->setContenu($filteredContent);
            
            $this->entityManager->flush();
            $this->addFlash('success', 'Commentaire modifié avec succès');
            return $this->redirectToRoute('commentaire_list');
        }

        return $this->render('commentaire/modForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'commentaire_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(int $id): Response
    {
        $commentaire = $this->commentaireRepository->find($id);
        
        if (!$commentaire) {
            throw $this->createNotFoundException('Commentaire non trouvé');
        }
        
        // Check if the current user is the owner of the comment
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer un commentaire.');
            return $this->redirectToRoute('app_login');
        }
        
        if ($commentaire->getUtilisateur() !== $currentUser && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce commentaire.');
            return $this->redirectToRoute('commentaire_list');
        }

        $this->entityManager->remove($commentaire);
        $this->entityManager->flush();
        $this->addFlash('success', 'Commentaire supprimé avec succès');

        return $this->redirectToRoute('commentaire_list');
    }
    
    /**
     * Send email notifications for a new comment
     * 
     * @param Commentaire $commentaire The new comment
     * @return void
     */
    private function sendCommentNotifications(Commentaire $commentaire): void
    {
        // Send notification to admin for moderation
        $this->emailService->sendCommentModerationNotification($commentaire);
    }
}
