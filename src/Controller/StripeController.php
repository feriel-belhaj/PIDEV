<?php

namespace App\Controller;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Repository\CommandeRepository;
//
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


//
use Doctrine\ORM\EntityManagerInterface;

class StripeController extends AbstractController
{
    #[Route('/create-checkout-session/{id}', name: 'stripe_checkout', methods: ['POST'])]
    public function checkout(int $id, CommandeRepository $commandeRepository): JsonResponse
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            return new JsonResponse(['error' => 'Commande introuvable'], 404);
        }

       
        $stripeSecretKey = $this->getParameter('stripe_secret_key');
        if (!$stripeSecretKey) {
            return new JsonResponse(['error' => 'Clé Stripe manquante'], 500);
        }

        
        Stripe::setApiKey($stripeSecretKey);

       
        $prixCentimes = intval(round($commande->getPrix() * 100));

        
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => 'Commande #' . $commande->getId()],
                        'unit_amount' => $prixCentimes, 
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('stripe_success', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL), // ✅ URL absolue
                'cancel_url' => $this->generateUrl('stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL), 
            ]);

            return new JsonResponse(['id' => $session->id]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur Stripe : ' . $e->getMessage()], 500);
        }
    }

    #[Route('/payment/success/{id}', name: 'stripe_success')]
    public function success(int $id, CommandeRepository $commandeRepository, EntityManagerInterface $em, MailerInterface $mailer)
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            return $this->redirectToRoute('app_home'); 
        }

    
        $commande->setEntityManager($em);

       
        $commande->setStatut('en cours');
        
       
        $em->flush();

        
        $email = (new Email())
            ->from('kridtaoufik994@gmail.com') // Expéditeur
            ->to('taoufik.krid.949@gmail.com') // Destinataire
            ->subject('Confirmation de commande')
            ->text('Votre commande a été traitée avec succès !')
            ->html('<p>Votre commande a été traitée avec succès ! Merci pour votre achat.</p>');

        
        $mailer->send($email);

        
        return $this->render('commande/success.html.twig', [
            'commande' => $commande,
            'message' => 'Paiement réussi ! Merci pour votre achat.',
        ]);
    }


    #[Route('/payment/cancel', name: 'stripe_cancel')]
    public function cancel(): JsonResponse
    {
        return new JsonResponse(['message' => 'Paiement annulé']);
    }
}
