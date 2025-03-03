<?php

namespace App\Controller;
//
use App\Entity\Produit;
use App\Repository\ProduitRepository;
//
use App\Entity\Commande;
use App\Entity\Utilisateur;
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
/////pagination
use Knp\Component\Pager\PaginatorInterface;
///pdf
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\SecurityBundle\Security;
//
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/commande')]
final class CommandeController extends AbstractController
{
    



    #[Route(name: 'app_commande_index', methods: ['GET', 'POST'])]
    public function index(CommandeRepository $commandeRepository, ProduitRepository $produitRepository, EntityManagerInterface $em, Request $request): Response
{
    
    $fidelite_points = 100;
    $fidelite_utilisee = false;

    
    if ($request->getSession()->has('fidelite_utilisee') && $request->getSession()->get('fidelite_utilisee')) {
        $fidelite_utilisee = true; 
        $fidelite_points = 0; 
    }

    
    if ($request->isMethod('POST') && $request->request->get('appliquer_fidelite') && !$fidelite_utilisee) {
        $commande_id = $request->request->get('commande_id');
        $commande = $commandeRepository->find($commande_id);

        
        if ($commande && $fidelite_points >= 100 && !$fidelite_utilisee) {
            
            $commande->setPrix($commande->getPrix() * 0.9); 
            
           
            $request->getSession()->set('fidelite_utilisee', true); 
            $fidelite_points -= 100; 
            $fidelite_utilisee = true; 
        }
    }

    
    $commandes = $commandeRepository->findAll();
    foreach ($commandes as $commande) {
        $commande->setEntityManager($em);
    }

    $produits = $produitRepository->findAll();
    $categories = Produit::getUniqueCategories($produits);

    
    return $this->render('commande/index.html.twig', [
        'commandes' => $commandes,
        'categories' => $categories,
        'produits' => $produits,
        'fidelite_points' => $fidelite_points,
        'fidelite_utilisee' => $fidelite_utilisee,
    ]);
}


    

    
    
    //
    #[Route('/commande/new', name: 'app_commande_new', methods: ['POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, ProduitRepository $produitRepository,Security $security): Response
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
            
            if ($quantite > $produit->getQuantitestock()) { 
                $this->addFlash('error', "La quantité demandée pour le produit {$produit->getNom()} est supérieure au stock disponible.");
                return $this->redirectToRoute('app_produit_index');
            }

            
            $commande->addProduit($produit);

           
            $totalPrix += $produit->getPrix() * $quantite;

            
            $nouveauStock = $produit->getQuantitestock() - $quantite;
            $produit->setQuantitestock($nouveauStock);

            
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
public function index2(
    Request $request, 
    CommandeRepository $commandeRepository, 
    EntityManagerInterface $em,
    PaginatorInterface $paginator
): Response
{
    
    $statut = $request->query->get('statut', '');
    $prixMin = $request->query->get('prixMin', null);
    $dateDebut = $request->query->get('dateDebut', null);
    $dateFin = $request->query->get('dateFin', null);
    $tri = $request->query->get('tri', 'id');
    

    
    $queryBuilder = $commandeRepository->createQueryBuilder('c')
        ->leftJoin('c.produit', 'p')
        ->addSelect('p');

    
    if (!empty($statut)) {
        $queryBuilder->andWhere('c.statut = :statut')
                     ->setParameter('statut', $statut);
    }
    if ($prixMin !== null) {
        $queryBuilder->andWhere('c.prix >= :prixMin')
                     ->setParameter('prixMin', $prixMin);
    }
    if ($dateDebut !== null) {
        $queryBuilder->andWhere('c.datecmd >= :dateDebut')
                     ->setParameter('dateDebut', $dateDebut);
    }
    
    if ($dateFin !== null) {
        $queryBuilder->andWhere('c.datecmd <= :dateFin')
                     ->setParameter('dateFin', $dateFin);
    }
    switch ($tri) {
        case 'prix':
            $queryBuilder->orderBy('c.prix', 'ASC'); 
            break;
        case 'datecmd':
            $queryBuilder->orderBy('c.datecmd', 'ASC'); 
            break;
        case 'statut':
            $queryBuilder->orderBy('c.statut', 'ASC'); 
            break;
        default:
            $queryBuilder->orderBy('c.id', 'ASC'); 
            break;
    }

    
    if (empty($statut) && $prixMin === null && $dateDebut === null && $dateFin === null) {
        $queryBuilder = $commandeRepository->createQueryBuilder('c'); 
    }
    
    $page = $request->query->getInt('page', 1);
    $commandes = $paginator->paginate(
        $queryBuilder, 
        $page, 
        3
    );

    
    $totalCommandes = $commandeRepository->count([]);


    $query = $em->createQuery('SELECT COUNT(c) FROM App\Entity\Commande c WHERE c.statut = :statut');
    $query->setParameter('statut', 'En cours');
    $commandesEnCours = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT COUNT(c) FROM App\Entity\Commande c WHERE c.statut = :statut');
    $query->setParameter('statut', 'Terminé');
    $commandesTerminees = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT COUNT(c) FROM App\Entity\Commande c WHERE c.statut = :statut');
    $query->setParameter('statut', 'Annulé');
    $commandesAnnulees = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT AVG(c.prix) FROM App\Entity\Commande c');
    $montantMoyen = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT COUNT(c) FROM App\Entity\Commande c WHERE c.statut = :statut');
    $query->setParameter('statut', 'En attente');
    $commandesEnAttente = $query->getSingleScalarResult();

   
    $tauxAnnulation = $totalCommandes > 0 ? ($commandesAnnulees / $totalCommandes) * 100 : 0;

    
    $queryProduits = $em->createQuery('
        SELECT p.id, p.nom, COUNT(c.id) AS total
        FROM App\Entity\Commande c
        JOIN c.produit p
        GROUP BY p.id
        ORDER BY total DESC
    ');
    $topProduits = $queryProduits->setMaxResults(5)->getResult();

   
    $queryCategories = $em->createQuery('
        SELECT p.categorie, COUNT(p.id) AS total
        FROM App\Entity\Commande c
        JOIN c.produit p
        GROUP BY p.categorie
        ORDER BY total DESC
    ');
    $topCategories = $queryCategories->setMaxResults(5)->getResult();

    
    $query = $em->createQuery('SELECT SUM(c.prix) FROM App\Entity\Commande c');
    $chiffreAffairesTotal = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT SUM(c.prix) FROM App\Entity\Commande c WHERE c.statut = :statut');
    $query->setParameter('statut', 'Terminé');
    $chiffreAffairesTerminees = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT SUM(c.prix) FROM App\Entity\Commande c WHERE c.statut = :statut');
    $query->setParameter('statut', 'En cours');
    $chiffreAffairesEnCours = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT SUM(c.prix) FROM App\Entity\Commande c WHERE c.statut = :statut');
    $query->setParameter('statut', 'Annulé');
    $chiffreAffairesAnnulees = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT SUM(c.prix) FROM App\Entity\Commande c WHERE c.statut = :statut');
    $query->setParameter('statut', 'En attente');
    $chiffreAffairesEnAttente = $query->getSingleScalarResult();

    foreach ($commandes as $commande) {
        $commande->setEntityManager($em);
    }

    $quantiteTotale = 0;
    $totalCommandesAvecProduits = 0;
    
    foreach ($commandes as $commande) {
        $quantiteCommande = 0;
        foreach ($commande->getProduit() as $produit) {
            $quantiteCommande += $commande->getQuantiteProduit($produit);
        }
    
        if ($quantiteCommande > 0) {
            $quantiteTotale += $quantiteCommande;
            $totalCommandesAvecProduits++;
        }
    }
    
    $produitsParCommande = $totalCommandesAvecProduits > 0 ? $quantiteTotale / $totalCommandesAvecProduits : 0;
    
    $query = $em->createQuery('SELECT MIN(c.datecmd) FROM App\Entity\Commande c');
    $datePremiereCommande = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT MAX(c.datecmd) FROM App\Entity\Commande c');
    $dateDerniereCommande = $query->getSingleScalarResult();

    if ($datePremiereCommande && $dateDerniereCommande) {
        $datePremiereCommande = new \DateTime($datePremiereCommande);
        $dateDerniereCommande = new \DateTime($dateDerniereCommande);
        $interval = $datePremiereCommande->diff($dateDerniereCommande);
        $nombreDeJours = max(1, $interval->days);
        $commandesParJour = $totalCommandes / $nombreDeJours;
    } else {
        $commandesParJour = 0;
    }

    // Calcul du produit le plus rentable
    $produitLePlusRentable = null;
    $maxRevenue = 0;
    foreach ($topProduits as $produit) {
        if (isset($produit['total'], $produit['prix'], $produit['nom'])) {
            $revenuProduit = $produit['total'] * $produit['prix'];
            if ($revenuProduit > $maxRevenue) {
                $maxRevenue = $revenuProduit;
                $produitLePlusRentable = $produit;
            }
        }
    }
    $nomProduitRentable = $produitLePlusRentable ? $produitLePlusRentable['nom'] : 'N/A';

    ///new
    $queryProduit = $em->createQuery('
    SELECT p.id, p.nom, COUNT(c.id) AS total
    FROM App\Entity\Commande c
    JOIN c.produit p
    GROUP BY p.id
    ORDER BY total DESC
')
->setMaxResults(1);

$produitLePlusCommande = $queryProduit->getOneOrNullResult();

$nomProduitLePlusCommande = $produitLePlusCommande ? $produitLePlusCommande['nom'] : 'Aucun produit';

    return $this->render('commande/admin_commande_index.html.twig', [
        'commandes' => $commandes,
        'totalCommandes' => $totalCommandes,
        'commandesEnCours' => $commandesEnCours,
        'commandesTerminees' => $commandesTerminees,
        'commandesAnnulees' => $commandesAnnulees,
        'montantMoyen' => $montantMoyen,
        'commandesEnAttente' => $commandesEnAttente,
        'tauxAnnulation' => $tauxAnnulation,
        'commandesParJour' => $commandesParJour,
        'topProduits' => $topProduits,
        'topCategories' => $topCategories,
        'chiffreAffairesTotal' => $chiffreAffairesTotal,
        'chiffreAffairesTerminees' => $chiffreAffairesTerminees,
        'chiffreAffairesEnCours' => $chiffreAffairesEnCours,
        'chiffreAffairesAnnulees' => $chiffreAffairesAnnulees,
        'chiffreAffairesEnAttente' => $chiffreAffairesEnAttente,
        'produitsParCommande' => $produitsParCommande,
        'nomProduitRentable' => $nomProduitRentable,
        'nomProduitLePlusCommande' => $nomProduitLePlusCommande,
        'maxRevenue' => $maxRevenue,
        'statutActuel' => $statut,
        'prixMin' => $prixMin,
        'dateDebut' => $dateDebut,
        'dateFin' => $dateFin,
        'tri' => $tri,
    ]);
}


///pdf
#[Route('/admin/commandes/pdf', name:'admin_commande_export_pdf')]
public function exportPdf(CommandeRepository $commandeRepository, EntityManagerInterface $em): Response
{
    
    $commandes = $commandeRepository->findAll();

    foreach ($commandes as $commande) {
        $commande->setEntityManager($em);
    }

    
    $totalCommandes = count($commandes);

   
    $query = $em->createQuery('SELECT SUM(c.prix) FROM App\Entity\Commande c');
    $chiffreAffairesTotal = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT COUNT(c) FROM App\Entity\Commande c WHERE c.statut = :statut');
    $query->setParameter('statut', 'Terminé');
    $commandesTerminees = $query->getSingleScalarResult();

    
    $query = $em->createQuery('SELECT COUNT(c) FROM App\Entity\Commande c WHERE c.statut = :statut');
    $query->setParameter('statut', 'Annulé');
    $commandesAnnulees = $query->getSingleScalarResult();

   
    $query = $em->createQuery('SELECT AVG(c.prix) FROM App\Entity\Commande c');
    $montantMoyen = $query->getSingleScalarResult();
    
       $query = $em->createQuery('SELECT MIN(c.datecmd) FROM App\Entity\Commande c');
       $datePremiereCommande = $query->getSingleScalarResult();
   
       
       $query = $em->createQuery('SELECT MAX(c.datecmd) FROM App\Entity\Commande c');
       $dateDerniereCommande = $query->getSingleScalarResult();
   
       
       if ($datePremiereCommande && $dateDerniereCommande) {
           $datePremiereCommande = new \DateTime($datePremiereCommande);
           $dateDerniereCommande = new \DateTime($dateDerniereCommande);
   
           
           $interval = $datePremiereCommande->diff($dateDerniereCommande);
           $nombreDeJours = max(1, $interval->days); 
   
           
           $commandesParJour = $totalCommandes / $nombreDeJours;
       } else {
           $commandesParJour = 0;
       }

    
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true); 
    $dompdf = new Dompdf($options);

    
    $html = $this->renderView('commande/pdf_export.html.twig', [
        'commandes' => $commandes,
        'totalCommandes' => $totalCommandes,           
        'chiffreAffairesTotal' => $chiffreAffairesTotal,  
        'commandesTerminees' => $commandesTerminees,   
        'commandesAnnulees' => $commandesAnnulees,     
        'commandesParJour' => $commandesParJour,
        'montantMoyen' => $montantMoyen                
    ]);

    
    $dompdf->loadHtml($html);

   
    $dompdf->setPaper('A4', 'portrait');

   
    $dompdf->render();

    
    $pdfContent = $dompdf->output();
    return new Response($pdfContent, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="commandes.pdf"',
    ]);
}
#[Route('/commande/edit/{id}', name: 'app_commande_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
{
    $commande->setEntityManager($entityManager);

    $produits = $commande->getProduit();
    $quantitesData = [];

    foreach ($produits as $produit) {
        $quantitesData[$produit->getId()] = $commande->getQuantiteProduit($produit);
    }

    $form = $this->createFormBuilder(['quantites' => $quantitesData])
        ->add('quantites', CollectionType::class, [
            'entry_type' => IntegerType::class,
            'entry_options' => [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La quantité ne peut pas être vide.']),
                    new Assert\Positive(['message' => 'La quantité doit être un nombre positif.']),
                    new Assert\Callback(function ($quantite, ExecutionContextInterface $context) use ($produits) {
                        $index = $context->getPropertyPath(); 
                        preg_match('/\[(\d+)\]$/', $index, $matches);

                        if (!isset($matches[1])) {
                            return;
                        }

                        $produitId = (int) $matches[1];
                        $produit = $produits[$produitId] ?? null;

                        if ($produit && $quantite > $produit->getQuantitestock()) {
                            $context->buildViolation('La quantité demandée dépasse le stock disponible (Max: ' . $produit->getQuantitestock() . ').')
                                ->atPath('quantites[' . $produitId . ']')  
                                ->addViolation();
                        }
                    }),
                ],
                'attr' => ['class' => 'form-control'],
            ],
            'allow_add' => false,
            'allow_delete' => false,
            'label' => false,
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && !$form->isValid()) {
        $this->addFlash('error', "Corrigez les erreurs dans le formulaire.");
    }

    if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();
        $quantites = $data['quantites'];

        foreach ($produits as $produit) {
            if (isset($quantites[$produit->getId()]) && is_numeric($quantites[$produit->getId()])) {
                $nouvelleQuantite = (int) $quantites[$produit->getId()];

                if ($nouvelleQuantite > $produit->getQuantitestock()) {
                    $this->addFlash('error', "La quantité pour {$produit->getNom()} dépasse le stock disponible !");
                    return $this->redirectToRoute('app_commande_edit', ['id' => $commande->getId()]);
                }

                $commande->setQuantiteProduit($produit, $nouvelleQuantite, $entityManager);
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
            // Le champ prix est supprimé
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
    
    ////metier
   

}


