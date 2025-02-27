<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\FormationReservee;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FormationReserveeController extends AbstractController
{
    #[Route('/formation/{id}/reserver', name: 'app_formation_reserver')]
    public function reserver(Formation $formation, EntityManagerInterface $entityManager): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();
        
        if (!$utilisateur) {
            $this->addFlash('error', 'Vous devez être connecté pour réserver une formation');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'utilisateur n'a pas déjà réservé cette formation
        $existingReservation = $entityManager->getRepository(FormationReservee::class)->findOneBy([
            'formation' => $formation,
            'utilisateur' => $utilisateur
        ]);

        if ($existingReservation) {
            $this->addFlash('warning', 'Vous avez déjà réservé cette formation');
            return $this->redirectToRoute('app_formation_show', ['id' => $formation->getId()]);
        }

        // Vérifier s'il reste des places
        if ($formation->getNbparticipant() >= $formation->getNbplace()) {
            $this->addFlash('error', 'Désolé, il n\'y a plus de places disponibles pour cette formation');
            return $this->redirectToRoute('app_formation_show', ['id' => $formation->getId()]);
        }

        $reservation = new FormationReservee();
        $reservation->setFormation($formation);
        $reservation->setUtilisateur($utilisateur);
        $reservation->setNom($utilisateur->getNom());
        $reservation->setPrenom($utilisateur->getPrenom());

        // Incrémenter le nombre de participants
        $formation->setNbparticipant($formation->getNbparticipant() + 1);

        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été enregistrée avec succès');
        return $this->redirectToRoute('app_formation_show', ['id' => $formation->getId()]);
    }

    #[Route('/artisan/formations-reservees', name: 'app_artisan_formations_reservees')]
    #[IsGranted('ROLE_ARTISAN')]
    public function artisanList(EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(FormationReservee::class)->findAll();

        return $this->render('formation_reservee/artisan_list.html.twig', [
            'reservations' => $reservations
        ]);
    }
} 