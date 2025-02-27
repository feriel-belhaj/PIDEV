<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;

// Ajout des use pour Stripe
require_once __DIR__ . '/../../vendor/stripe/stripe-php/init.php';
use \Stripe\Stripe;
use \Stripe\Checkout\Session;

class StripeController extends AbstractController
{
    private $security;
    private $mailer;

    public function __construct(Security $security, MailerInterface $mailer)
    {
        $this->security = $security;
        $this->mailer = $mailer;
    }

    #[Route('/stripe/checkout/{id}', name: 'app_stripe_checkout')]
    public function checkout(Formation $formation): Response
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $formation->getTitre(),
                        'description' => $formation->getDescription(),
                    ],
                    'unit_amount' => $formation->getPrix() * 100, // Le prix doit être en centimes
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/stripe/success', name: 'app_stripe_success')]
    public function success(Request $request): Response
    {
        /** @var Utilisateur $user */
        $user = $this->security->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non connecté');
            return $this->render('stripe/success.html.twig');
        }

        try {
            $userEmail = $user->getEmail();
            if (empty($userEmail)) {
                throw new \Exception('L\'email de l\'utilisateur est vide');
            }

            // Configuration transport avec TLS
            $dsn = 'smtp://anasallam02@gmail.com:edgenogpcelidmwc@smtp.gmail.com:587?encryption=tls&auth_mode=login';
            $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);

            // Création d'un nouveau mailer avec ce transport
            $mailer = new \Symfony\Component\Mailer\Mailer($transport);

            $email = (new Email())
                ->from('anasallam02@gmail.com')
                ->to($userEmail)
                ->subject('Confirmation de votre paiement')
                ->text('Votre paiement a été confirmé.')
                ->html($this->renderView(
                    'emails/payment_success.html.twig',
                    ['user' => $user]
                ));

            // Envoi avec logging détaillé
            try {
                error_log("Début de l'envoi de l'email");
                $mailer->send($email);
                error_log("Email envoyé avec succès à " . $userEmail);
                $this->addFlash('success', 'Email de confirmation envoyé !');
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                error_log("Erreur transport email : " . $e->getMessage());
                error_log("Code erreur : " . $e->getCode());
                error_log("Trace : " . $e->getTraceAsString());
                throw $e;
            }

        } catch (\Exception $e) {
            error_log("ERREUR GÉNÉRALE : " . $e->getMessage());
            error_log("Trace : " . $e->getTraceAsString());
            
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }

        return $this->render('stripe/success.html.twig', [
            'message' => 'Paiement réussi ! Merci pour votre achat.'
        ]);
    }

    #[Route('/stripe/cancel', name: 'app_stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }
} 