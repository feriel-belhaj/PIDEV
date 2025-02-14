<?php

namespace App\Controller;
//
use App\Entity\Produit;
use App\Repository\ProduitRepository;
//
use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
///
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
///controle de saisie
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
//
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

#[Route('/commande')]
final class CommandeController extends AbstractController
{
    #[Route(name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository, ProduitRepository $produitRepository,  EntityManagerInterface $em): Response
    {
        
    $commandes = $commandeRepository->findAll();

    
    foreach ($commandes as $commande) {
        $commande->setEntityManager($em);
    }


        
        $produits = $produitRepository->findAll();

       
        $categories = Produit::getUniqueCategories($produits);

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'categories' => $categories, 
            'produits' => $produits 
        ]);
    }
    //
    #[Route('/commande/new', name: 'app_commande_new', methods: ['POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
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

    

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

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
    

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

    ///back
    #[Route('/admin/commandes', name: 'admin_commande_index')]
    public function index2(CommandeRepository $commandeRepository, ProduitRepository $produitRepository,EntityManagerInterface $em): Response
    {

       
        $commandes = $commandeRepository->findAll();
        foreach ($commandes as $commande) {
            $commande->setEntityManager($em);
        }

       
        return $this->render('commande/admin_commande_index.html.twig', [
            'commandes' => $commandes,
        ]);
    }
    #[Route('/admin/commande/{id}', name: 'admin_commande_delete', methods: ['POST'])]
    public function deleteback(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        
        if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->request->get('_token'))) {
            
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        
        return $this->redirectToRoute('admin_commande_index');
    }
    ///
    #[Route('/admin/commande/edit/{id}', name: 'admin_commande_edit', methods: ['GET', 'POST'])]
    public function editBack(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($commande)
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'En attente',
                    'En cours' => 'En cours',
                    'Terminé' => 'Terminé',
                    'Annulé' => 'Annulé',
                ],
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-control', 
                ]
            ])
            ->add('prix', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.01',
                    'min' => '0',
                ],
                'constraints' => [
                    new Assert\Positive(message: 'Le prix doit être un nombre positif.'),
                    new Assert\LessThanOrEqual([
                        'value' => $commande->getPrix(),
                        'message' => 'Le prix ne peut pas être supérieur au prix initial.',
                    ]),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Statut de la commande mis à jour avec succès.');
            return $this->redirectToRoute('admin_commande_index'); 
        }

        return $this->render('commande/admin_commande_edit.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande,
        ]);
    }
}
