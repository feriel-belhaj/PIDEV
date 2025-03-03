<?php

namespace App\Controller;
use App\Entity\Formation;
use App\Entity\Certificat;
use App\Form\CertificatType;
use App\Repository\CertificatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\FormationReservee;

#[Route('/certificat')]
final class CertificatController extends AbstractController
{
    #[Route('/', name: 'app_certificat_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $certificats = $entityManager
            ->getRepository(Certificat::class)
            ->findAll();

        // Récupérer les formations réservées
        $formationsReservees = $entityManager
            ->getRepository(FormationReservee::class)
            ->findAll();

        return $this->render('certificat/index.html.twig', [
            'certificats' => $certificats,
            'formationsReservees' => $formationsReservees,
        ]);
    }

    #[Route('/new', name: 'app_certificat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $certificat = new Certificat();
        $certificat->setDateobt(new \DateTime());
        
        $form = $this->createForm(CertificatType::class, $certificat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($certificat);
            $entityManager->flush();

            return $this->redirectToRoute('app_certificat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('certificat/new.html.twig', [
            'certificat' => $certificat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_certificat_show', methods: ['GET'])]
    public function show(Certificat $certificat): Response
    {
        return $this->render('certificat/show.html.twig', [
            'certificat' => $certificat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_certificat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Certificat $certificat, EntityManagerInterface $entityManager): Response
    {
        // Vérifier et initialiser les champs si null
        if ($certificat->getNom() === null) {
            $certificat->setNom('');
        }
        if ($certificat->getPrenom() === null) {
            $certificat->setPrenom('');
        }
        if ($certificat->getDateobt() === null) {
            $certificat->setDateobt(new \DateTime());
        }
        if ($certificat->getNiveau() === null) {
            $certificat->setNiveau('');
        }
        if ($certificat->getNomorganisme() === null) {
            $certificat->setNomorganisme('');
        }

        $form = $this->createForm(CertificatType::class, $certificat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_certificat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('certificat/edit.html.twig', [
            'certificat' => $certificat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_certificat_delete', methods: ['POST'])]
    public function delete(Request $request, Certificat $certificat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$certificat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($certificat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_certificat_index', [], Response::HTTP_SEE_OTHER);
    }
}
