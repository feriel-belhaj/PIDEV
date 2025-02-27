<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
///image
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/produit')]
final class ProduitController extends AbstractController
{
    #[Route(name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();
        $categories = Produit::getUniqueCategories($produits);

        
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
            'categories' => $categories
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,Security $security): Response
    {
        $utilisateur = $security->getUser(); // Récupérer l'utilisateur connecté

        if ( !$utilisateur instanceof Utilisateur) {
            throw new AccessDeniedException('Vous devez être connecté pour ajouter un produit.');
        }

        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur->addProduit($produit);
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/admin/produit/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('admin_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('produit/edit.html.twig', [
        'produit' => $produit,
        'form' => $form->createView(),
    ]);
    }
    
    

    #[Route('/admin/produit/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_produit_index', [], Response::HTTP_SEE_OTHER);
    }
   

    //////backend
    #[Route('/admin/produits', name: 'admin_produit_index')]
public function adminIndex(EntityManagerInterface $entityManager): Response
{
    $produits = $entityManager->getRepository(Produit::class)->findAll();

    return $this->render('produit/admin_produit_index.html.twig', [
        'produits' => $produits,
    ]);
    
}
#[Route('/admin/produit', name: 'admin_produit_index')]
public function index2(Request $request, EntityManagerInterface $entityManager): Response
{
    
    $produits = $entityManager->getRepository(Produit::class)->findAll();

    
    $produit = new Produit();
    $form = $this->createForm(ProduitType::class, $produit);

    
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension(); 
            try {
                $imageFile->move(
                    $this->getParameter('uploads_directory'), 
                    $newFilename
                );
                $produit->setImage($newFilename);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                return $this->redirectToRoute('admin_produit_index');
            }
        }

        $entityManager->persist($produit);
        $entityManager->flush();

        $this->addFlash('success', 'Produit ajouté avec succès !');

        return $this->redirectToRoute('admin_produit_index');
    }

    return $this->render('produit/admin_produit_index.html.twig', [
        'produits' => $produits,
        'form' => $form->createView(), 
    ]);

}
}
