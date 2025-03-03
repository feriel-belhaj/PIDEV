<?php

namespace App\Controller;

use App\Entity\Don;
use App\Form\DonType;
use App\Repository\DonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Evenement;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Utilisateur;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/don')]
final class DonController extends AbstractController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route(name: 'app_don_index', methods: ['GET'])]
    public function index(DonRepository $donRepository): Response
    {
        return $this->render('don/index.html.twig', [
            'dons' => $donRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_don_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Security $security, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour faire un don.');
        }

        $don = new Don();
        $don->setEvenement($evenement);
        $form = $this->createForm(DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $security->getUser();
            
            if ( $utilisateur instanceof Utilisateur) {
                $don->setCreateur($utilisateur);
                
            }
            
            // On stocke temporairement les données du don dans la session
            $this->requestStack->getSession()->set('temp_don', [
                'amount' => $don->getAmount(),
                'message' => $don->getMessage(),
                'evenement_id' => $evenement->getId()
            ]);

            return $this->redirectToRoute('create_checkout_session');
        }

        return $this->render('don/new.html.twig', [
            'don' => $don,
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_don_show', methods: ['GET'])]
    public function show(Don $don): Response
    {
        return $this->render('don/show.html.twig', [
            'don' => $don,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_don_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Don $don, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_don_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('don/edit.html.twig', [
            'don' => $don,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_don_delete', methods: ['POST'])]
    public function delete(Request $request, Don $don, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$don->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($don);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_don_index', [], Response::HTTP_SEE_OTHER);
    }
}
