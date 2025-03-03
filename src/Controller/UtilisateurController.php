<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\CandidatureRepository;
use App\Repository\CommandeRepository;
use App\Repository\DonRepository;
use App\Repository\EvenementRepository;
use App\Repository\FormationRepository;
use App\Repository\PartenariatRepository;
use App\Repository\ProduitRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;



#[Route('/utilisateur')]
final class UtilisateurController extends AbstractController
{

    #[Route('/home', name: 'app_home')]
    public function home(Security $security): Response
    {
        
        $user = $security->getUser();

        return $this->render('base.html.twig', [
            'user' => $user,  
        ]);
    } 
    

    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request,EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer ): Response
    {

        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
            $utilisateur->setPassword($hashedPassword);

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                } catch (FileException $e) {
                    throw new \Exception("Impossible d'enregistrer l'image");
                }
                $utilisateur->setImage($newFilename);
            } else {
                $utilisateur->setImage('../../assets/img/avatars/profile.jpg');
            }

            $utilisateur->setDateInscription(new \DateTime());
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $email = (new Email())
            ->from('chakrounfatma23@gmail.com') 
            ->to($utilisateur->getEmail()) 
            ->subject('Bienvenue sur notre plateforme ARTIZINA')
            ->text('Merci de vous être inscrit sur notre plateforme. Nous sommes ravis de vous accueillir.');

            $mailer->send($email);

            return $this->redirectToRoute('app_home');
        }

        return $this->render('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        
        $form = $this->createForm(UtilisateurType::class, $utilisateur, [
            'is_back_office' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
            $utilisateur->setPassword($hashedPassword);
            
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                } catch (FileException $e) {
                    throw new \Exception("Impossible d'enregistrer l'image");
                }
                $utilisateur->setImage($newFilename);
            } else {
                $utilisateur->setImage('../../assets/img/avatars/profile.jpg');
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
            
        ]);
    }
    #[Route('/profil', name: 'app_profil')]
    public function profil(Security $security
    ,CandidatureRepository $candidatureRepository
    ,PartenariatRepository $partenariatRepository
    ,FormationRepository $formationRepository
    ,EvenementRepository $evenementRepository
    ,DonRepository $donRepository
    ,ProduitRepository $produitRepository
    ,CommandeRepository $commandeRepository): Response
    {
        
        $user = $security->getUser();
        $commandes = $commandeRepository->findBy(['createur' => $user]);
        $evenements = $evenementRepository->findBy(['createur' => $user]);
        $partenariats = $partenariatRepository->findBy(['createur' => $user]);
        $produits = $produitRepository->findBy(['createur' => $user]);
        $candidatures = $candidatureRepository->findBy(['createur' => $user]);
        $don = $donRepository->findBy(['createur' => $user]);
        $formation = $formationRepository->findBy(['createur' => $user]);

        return $this->render('utilisateur/profile.html.twig', [
            'utilisateur' => $user,  
            'commandes' => $commandes,
            'evennements' => $evenements,
            'partenariats' => $partenariats,
            'produits' => $produits,
            'candidatures' => $candidatures,
            'dons' => $don,
            'formation' => $formation
        ]);
    } 
    
   
    
    #**************** BACK OFFICE* ***********************
    #[Route('/homeBack', name: 'app_homeBack')]
    public function homeBack(Security $security): Response
    {
        
        $user = $security->getUser();

        return $this->render('baseBack.html.twig', [
            'user' => $user,  
        ]);
    }

    #[Route('/back',name: 'app_utilisateur_back', methods: ['GET'])]
    public function index(Security $security,Request $request, PaginatorInterface $paginator,UtilisateurRepository $utilisateurRepository): Response
    {
        $user = $security->getUser();
        $query = $utilisateurRepository->createQueryBuilder('u')
        ->getQuery();

        $utilisateurs = $paginator->paginate(
            $query, 
            $request->query->getInt('page', 1), 
            5 
        );

    return $this->render('utilisateur/index.html.twig', [
        'utilisateurs' => $utilisateurs,
        'user' => $user, 
    ]);
    }
    #[Route('/back/search', name: 'app_utilisateur_search', methods: ['GET'])]
    public function search(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {
        $query = $request->query->get('query');

        $users = $utilisateurRepository->createQueryBuilder('u')
            ->where('u.nom LIKE :query')
            ->orWhere('u.prenom LIKE :query')
            ->orWhere('u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        return $this->render('utilisateur/_user_list.html.twig', [
            'utilisateurs' => $users,
        ]);
    }

    
    #[Route('/newBack', name: 'app_utilisateur_newBack', methods: ['GET', 'POST'])]
    public function newBack(Request $request,EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher ): Response
    {

        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur, [
            'is_back_office' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
            $utilisateur->setPassword($hashedPassword);

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                } catch (FileException $e) {
                    throw new \Exception("Impossible d'enregistrer l'image");
                }
                $utilisateur->setImage($newFilename);
            } else {
                $utilisateur->setImage('../../assets/img/avatars/profile.jpg');
            }

            $utilisateur->setDateInscription(new \DateTime());
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_back');
        }

        return $this->render('utilisateur/newBack.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'app_utilisateur_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }
    #[Route('/{id}/editBack', name: 'app_utilisateur_editBack', methods: ['GET', 'POST'])]
        public function editBack(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
        {
            $form = $this->createForm(UtilisateurType::class, $utilisateur, [
                'is_back_office' => true,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $plainPassword = $form->get('password')->getData();
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
                $utilisateur->setPassword($hashedPassword);
                $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                } catch (FileException $e) {
                    throw new \Exception("Impossible d'enregistrer l'image");
                }
                $utilisateur->setImage($newFilename);
            } else {
                $utilisateur->setImage('../../assets/img/avatars/profile.jpg');
            }

                $entityManager->flush();

                return $this->redirectToRoute('app_utilisateur_back', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('utilisateur/editBack.html.twig', [
                'utilisateur' => $utilisateur,
                'form' => $form,
            ]);
        }

        #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
        public function delete(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
        {
            if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->getPayload()->getString('_token'))) {
                $entityManager->remove($utilisateur);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_utilisateur_back', [], Response::HTTP_SEE_OTHER);
        }

        #[Route('/utilisateur/{id}/make-admin', name: 'app_utilisateur_make_admin')]
        public function makeAdmin(Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
        {
            // Vérifier si l'utilisateur est déjà un administrateur
            if ($utilisateur->getRole() === 'ROLE_ADMIN') {
                $this->addFlash('warning', 'Cet utilisateur est déjà un administrateur.');
            } else {
                
                $utilisateur->setRole('ROLE_ADMIN');
                $entityManager->flush();
   
                $this->addFlash('success', 'L\'utilisateur a été promu administrateur.');
            }

            // Rediriger vers la page des utilisateurs du back-office
            return $this->redirectToRoute('app_utilisateur_back');
        }
    
        #[Route('/statistiques/inscriptions', name: 'statistiques_inscriptions', methods: ['GET'])]
        public function inscriptionsParMois(ManagerRegistry $doctrine): JsonResponse
        {
            $conn = $doctrine->getConnection();
            
            $sql = "
                SELECT DATE_FORMAT(date_inscription, '%Y-%m') as mois, COUNT(id) as total
                FROM utilisateur
                GROUP BY mois
                ORDER BY mois
            ";
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->executeQuery()->fetchAllAssociative();

            $labels = array_column($result, 'mois');
            $data = array_column($result, 'total');

            return $this->json([
                'labels' => $labels,
                'data' => $data
            ]);
        }
        
        #[Route('/statistiques/sexe', name: 'statistiques_sexe', methods: ['GET'])]
        public function statistiquesSexe(UtilisateurRepository $utilisateurRepository): JsonResponse
        {
            // Utiliser le repository pour obtenir les données des sexes
            $sexes = $utilisateurRepository->countSexes();

            // Retourner les données sous forme de JSON
            return new JsonResponse($sexes);
        }
        #[Route('/test/testMail', name: 'app_testMail')]
        public function testMail(MailerInterface $mailer)
        {
            $email = (new Email())
            ->from('chakrounfatma23@gmail.com') // Ton adresse email
            ->to('chakrounfatma23@gmail.com') // L'email auquel tu veux envoyer
            ->subject('Test Email Symfony')
            ->text('Ceci est un test pour vérifier l\'envoi d\'email depuis Symfony.');

       
        $mailer->send($email);

        
        return $this->json([
            'message' => 'L\'email a été envoyé avec succès.'
        ]);
        }

}
