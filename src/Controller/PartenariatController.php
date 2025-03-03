<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Partenariat;
use App\Entity\Utilisateur;
use App\Form\PartenariatType;
use App\Repository\PartenariatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route('/partenariat')]
final class PartenariatController extends AbstractController
{
    #[Route('/', name: 'app_partenariat_index', methods: ['GET'])]
    public function index(PartenariatRepository $partenariatRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $dateDebut = $request->query->get('dateDebut');
        $dateFin = $request->query->get('dateFin');

        $queryBuilder = $partenariatRepository->findAllOrderedByIdDescQuery($dateDebut, $dateFin);

        // Récupérer le numéro de page à partir de la requête
        $currentPage = $request->query->getInt('page', 1);

        // Paginator prend la query, le nombre d'éléments par page, et le numéro de page actuel
        $pagination = $paginator->paginate(
            $queryBuilder, // La requête pour récupérer les partenariats
            $currentPage,   // Le numéro de la page actuelle
            6        // Le nombre d'éléments par page (ajustez selon vos besoins)
        );

        // Passer les variables 'pagination' à la vue
        return $this->render('partenariat/index.html.twig', [
            'pagination' => $pagination,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
        ]);
    }

    #[Route('/new', name: 'app_partenariat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $partenariat = new Partenariat();
        
        // Créer le formulaire
        $form = $this->createForm(PartenariatType::class, $partenariat, ['is_edit' => false]);
        
        // Traiter la soumission du formulaire
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
    
            // Si une image est téléchargée, on la sauvegarde
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('partenariat_images_directory'), $newFilename);
                $partenariat->setImage($newFilename);
            }
    
            // Vérification et assignation des dates par défaut si elles sont nulles
            if (!$partenariat->getDateDebut()) {
                $partenariat->setDateDebut(new \DateTime());  // Assigner la date actuelle par défaut
            }
    
            if (!$partenariat->getDateFin()) {
                $partenariat->setDateFin(new \DateTime());   // Assigner la date actuelle par défaut
            }
            $utilisateur = $security->getUser(); // Récupérer l'utilisateur connecté

            if ( $utilisateur instanceof Utilisateur) {
                $partenariat->setCreateur($utilisateur);
                $partenariat->addUtilisateur($utilisateur);
            }
    
            // Sauvegarder l'entité dans la base de données
            $entityManager->persist($partenariat);
            $entityManager->persist($utilisateur);
            $entityManager->flush();
    
            // Message de succès
            $this->addFlash('success', 'Partenariat ajouté avec succès.');
    
            // Rediriger vers la liste des partenariats
            return $this->redirectToRoute('app_partenariat_index');
        }
    
        // Affichage du formulaire
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