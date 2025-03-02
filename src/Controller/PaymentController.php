<?php
namespace App\Controller;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommandeRepository;

class PaymentController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout', methods: ['POST'])]
    public function checkout(Request $request, CommandeRepository $commandeRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $commandeId = $data['commande_id'] ?? null;

        if (!$commandeId) {
            return new JsonResponse(['error' => 'Commande ID manquant'], 400);
        }

        $commande = $commandeRepository->find($commandeId);
        if (!$commande) {
            return new JsonResponse(['error' => 'Commande non trouvée'], 404);
        }

        // Configure Stripe avec la clé secrète
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        // Création de la session de paiement
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => 'Commande #' . $commande->getId()],
                    'unit_amount' => $commande->getPrix() * 100, // Convertir en centimes
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_payment_success', [], 0),
            'cancel_url' => $this->generateUrl('app_payment_cancel', [], 0),
        ]);

        
        $stripePublicKey = 'pk_test_51QvhyiP7cyQsM3mKvIlQHhs6D8M9IugXYuKhXslzZ6ijoYc33Y4qKjEKcB2r4dpYQnn0ggQMoo86Ic9dZBaWOhBX00xs4rQYIs'; // Remplacez par votre clé publique réelle

       
        return $this->render('commande/index.html.twig', [
            'stripe_public_key' => $stripePublicKey,
            'session_id' => $session->id,
            'commande' => $commande,
        ]);
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function success(): JsonResponse
    {
        return new JsonResponse(['message' => 'Paiement réussi']);
    }

    #[Route('/payment/cancel', name: 'app_payment_cancel')]
    public function cancel(): JsonResponse
    {
        return new JsonResponse(['message' => 'Paiement annulé']);
    }
}
