<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commande')]
final class CommandeController extends AbstractController
{
    #[Route(name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }
<<<<<<< Updated upstream

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

=======
    //
    #[Route('/commande/new', name: 'app_commande_new', methods: ['POST'])]
    public function new(Request $request, Security $security, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        $commande = new Commande();
        $commande->setDatecmd(new \DateTime());
        $commande->setStatut("En attente");

        $totalPrix = 0;
        $data = $request->request->all();
        $produits = $data['produits'];

        if (!is_array($produits) || empty($produits)) {
            $this->addFlash('error', 'Aucun produit sélectionné.');
            return $this->redirectToRoute('app_commande_index');
        }

        
        $commande->setPrix(0);
        $utilisateur = $security->getUser();
            if ( $utilisateur instanceof Utilisateur) {
                $commande->setCreateur($utilisateur);
                
            }

        $entityManager->persist($commande);
        $entityManager->flush();

        foreach ($produits as $produitId => $data) {
            $produit = $produitRepository->find($produitId);

            $quantite = isset($data['quantite']) && is_numeric($data['quantite']) ? intval($data['quantite']) : 0;

            if ($produit && $quantite > 0) {
                $commande->addProduit($produit);
                $totalPrix += $produit->getPrix() * $quantite;

            
                $commande->setQuantiteProduit($produit, $quantite, $entityManager);
                $entityManager->flush();
            }
        }

        $commande->setPrix($totalPrix);

    
        $entityManager->flush();

        $this->addFlash('success', 'Commande enregistrée avec succès !');

        return $this->redirectToRoute('app_commande_index');
    }

    

>>>>>>> Stashed changes
    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

<<<<<<< Updated upstream
    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }
=======
    #[Route('/commande/edit/{id}', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
      
        $commande->setEntityManager($entityManager);
    
       
        $produits = $commande->getProduit();
    
       
        $quantitesData = [];
        foreach ($produits as $produit) {
            $quantitesData[] = $commande->getQuantiteProduit($produit);
        }
    
       
        $form = $this->createFormBuilder(['quantites' => $quantitesData])
            ->add('quantites', CollectionType::class, [
                'entry_type' => IntegerType::class,
                'entry_options' => [
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'La quantité ne peut pas être vide.']),
                        new Assert\Positive(['message' => 'La quantité doit être un nombre positif.']),
                    ],
                    'attr' => ['class' => 'form-control'],
                ],
                'allow_add' => false,
                'allow_delete' => false,
                'label' => false,
            ])
            ->getForm();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $quantites = $data['quantites'];
    
            
            foreach ($produits as $index => $produit) {
                if (isset($quantites[$index]) && is_numeric($quantites[$index])) {
                    $commande->setQuantiteProduit($produit, (int)$quantites[$index], $entityManager);
                }
            }
    
            $entityManager->flush();
    
            $this->addFlash('success', 'Commande mise à jour avec succès.');
            return $this->redirectToRoute('app_commande_index');
        }
    
        return $this->render('commande/edit.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande,
            'produits' => $produits, 
        ]);
    }
    
>>>>>>> Stashed changes

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}
