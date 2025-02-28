<?php

namespace App\Controller;

use App\Entity\Don;
use App\Entity\Evenement;
use App\Entity\Utilisateur;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Security;

class StripeController extends AbstractController
{
    private $requestStack;
    private $logger;
    private $mailer;
    private $security;

    public function __construct(
        RequestStack $requestStack, 
        LoggerInterface $logger,
        MailerInterface $mailer,
        Security $security
    ) {
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->security = $security;
    }

    #[Route('/create-checkout-session', name: 'create_checkout_session')]
    public function createCheckoutSession(EntityManagerInterface $entityManager): Response
    {
        $session = $this->requestStack->getSession();
        $tempDon = $session->get('temp_don');
        
        if (!$tempDon) {
            return $this->redirectToRoute('app_evenement_index');
        }

        $evenement = $entityManager->getRepository(Evenement::class)->find($tempDon['evenement_id']);

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        /** @var Utilisateur|null $user */
        $user = $this->getUser();
        if (!$user) {
            throw new \Exception('Utilisateur non connecté');
        }

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $tempDon['amount'] * 100,
                    'product_data' => [
                        'name' => 'Don pour : ' . $evenement->getTitre(),
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => [
                'evenement_id' => $tempDon['evenement_id'],
                'amount' => $tempDon['amount'],
                'message' => $tempDon['message'] ?? '',
                'user_email' => $user->getEmail()
            ],
            'success_url' => $this->generateUrl('payment_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('payment_cancel', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        // Sauvegarder l'ID de session Stripe
        $session->set('stripe_session_id', $checkout_session->id);

        return $this->redirect($checkout_session->url);
    }

    #[Route('/success-payment', name: 'payment_success')]
    public function successPayment(EntityManagerInterface $entityManager): Response
    {
        try {
            // Récupération du session_id
            $session_id = $this->requestStack->getSession()->get('stripe_session_id');
            if (!$session_id) {
                $session_id = $this->requestStack->getCurrentRequest()->query->get('session_id');
            }

            // Log pour debug
            $this->logger->info('Session ID récupéré', ['session_id' => $session_id]);

            if (!$session_id) {
                $this->logger->error('Session de paiement non trouvée');
                $this->addFlash('error', 'Session de paiement non trouvée.');
                return $this->redirectToRoute('app_evenement_index');
            }

            Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
            $stripeSession = Session::retrieve($session_id);
            
            // Log du statut de paiement
            $this->logger->info('Statut du paiement', [
                'payment_status' => $stripeSession->payment_status,
                'session_id' => $session_id
            ]);

            if ($stripeSession->payment_status !== 'paid') {
                throw new \Exception('Le paiement n\'a pas été complété');
            }

            // Récupérer les données depuis les métadonnées Stripe
            $metadata = (object)$stripeSession->metadata->toArray();
            
            // Log des métadonnées
            $this->logger->info('Métadonnées Stripe', [
                'metadata' => $metadata
            ]);

            // Récupérer l'utilisateur courant
            /** @var Utilisateur $user */
            $user = $this->security->getUser();
            if (!$user) {
                throw new \Exception('Utilisateur non connecté');
            }

            // Création et sauvegarde du don
            $don = new Don();
            $evenement = $entityManager->getRepository(Evenement::class)->find($metadata->evenement_id);
            
            if (!$evenement) {
                throw new \Exception('Événement non trouvé');
            }

            // Configurer le don
            $don->setEvenement($evenement);
            $don->setAmount($metadata->amount);
            $don->setMessage($metadata->message ?? '');
            $don->setPaymentref($session_id);
            $don->setDonationdate(new \DateTimeImmutable());
            $don->setUser($user); // Important : définir l'utilisateur

            // Persister les changements
            $entityManager->persist($don);
            $evenement->setCollectedamount($evenement->getCollectedamount() + $don->getAmount());
            $entityManager->flush();

            // Vérifier si l'événement a atteint 80% de son objectif
            $this->checkAndNotifyFundingProgress($evenement, $entityManager);

            // Configuration et envoi de l'email
            try {
                $dsn = $_ENV['MAILER_DSN'];
                $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
                $mailer = new \Symfony\Component\Mailer\Mailer($transport);

                $email = (new TemplatedEmail())
                    ->from('anasallam02@gmail.com')
                    ->to($metadata->user_email)
                    ->subject('Merci pour votre don !')
                    ->text(sprintf(
                        "Merci pour votre don de %s € pour l'événement \"%s\".\nVotre référence de paiement : %s",
                        $don->getAmount(),
                        $evenement->getTitre(),
                        $session_id
                    ))
                    ->htmlTemplate('emails/donation_thanks.html.twig')
                    ->context([
                        'amount' => $don->getAmount(),
                        'evenement' => $evenement,
                        'payment_ref' => $session_id
                    ]);

                $mailer->send($email);
                $this->logger->info('Email envoyé avec succès', [
                    'to' => $metadata->user_email
                ]);
            } catch (\Exception $emailError) {
                $this->logger->error('Erreur lors de l\'envoi de l\'email', [
                    'error' => $emailError->getMessage(),
                    'trace' => $emailError->getTraceAsString()
                ]);
                // On continue malgré l'erreur d'email
            }

            // Nettoyage de la session
            $this->requestStack->getSession()->remove('stripe_session_id');
            $this->requestStack->getSession()->remove('temp_don');

            // Redirection vers la page de succès
            return $this->render('stripe/success.html.twig', [
                'payment_data' => [
                    'amount' => $metadata->amount,
                    'evenement_id' => $metadata->evenement_id,
                    'email_sent' => isset($emailError) ? false : true
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du paiement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
            return $this->redirectToRoute('app_evenement_index');
        }
    }

    #[Route('/cancel-payment', name: 'payment_cancel')]
    public function cancelPayment(): Response
    {
        $session = $this->requestStack->getSession();
        $session->remove('temp_don');
        $session->remove('stripe_session_id');

        return $this->render('stripe/cancel.html.twig');
    }

    private function checkAndNotifyFundingProgress(Evenement $evenement, EntityManagerInterface $entityManager): void
    {
        try {
            // 1. Vérification des valeurs de base
            $objectif = $evenement->getGoalamount();
            $collected = $evenement->getCollectedamount();
            
            if ($objectif <= 0) {
                $this->logger->error('Objectif invalide', ['objectif' => $objectif]);
                return;
            }

            $percentage = ($collected / $objectif) * 100;

            // Log détaillé des calculs
            $this->logger->debug('Calculs de progression', [
                'objectif' => $objectif,
                'collected' => $collected,
                'percentage' => $percentage,
                'evenement_id' => $evenement->getId(),
                'evenement_titre' => $evenement->getTitre()
            ]);

            // 2. Vérification du seuil 99
            if ($percentage >= 88 && $percentage < 100) {
                $this->logger->debug('Seuil 88% atteint, vérification détaillée', [
                    'percentage_exact' => number_format($percentage, 4)
                ]);

                // 3. Vérification de l'utilisateur courant
                $currentUser = $this->security->getUser();
                if (!$currentUser) {
                    $this->logger->error('Utilisateur courant non trouvé');
                    return;
                }
                $currentUserId = $currentUser->getId();

                // 4. Vérification des dons existants pour cet événement
                $donsQuery = $entityManager->getRepository(Don::class)
                    ->createQueryBuilder('d')
                    ->select('d.id, d.user, d.amount')
                    ->where('d.evenement = :evenement')
                    ->setParameter('evenement', $evenement)
                    ->getQuery();

                $dons = $donsQuery->getResult();

                $this->logger->debug('Dons trouvés pour l\'événement', [
                    'nombre_dons' => count($dons),
                    'dons_details' => array_map(function($don) {
                        return [
                            'don_id' => $don['id'],
                            'user_id' => $don['user'] ? $don['user']->getId() : null,
                            'amount' => $don['amount']
                        ];
                    }, $dons)
                ]);

                // 5. Construction de la requête pour les donateurs
                $queryBuilder = $entityManager->getRepository(Utilisateur::class)
                    ->createQueryBuilder('u')
                    ->select('DISTINCT u.id, u.email, u.nom, u.prenom')
                    ->join('App\Entity\Don', 'd', 'WITH', 'd.user = u.id')
                    ->where('d.evenement = :evenement')
                    ->andWhere('u.id != :currentUserId')
                    ->setParameter('evenement', $evenement)
                    ->setParameter('currentUserId', $currentUserId);

                // Log de la requête SQL exacte
                $query = $queryBuilder->getQuery();
                $this->logger->debug('Requête SQL pour les donateurs', [
                    'sql' => $query->getSQL(),
                    'parameters' => $query->getParameters()->toArray(),
                    'current_user_id' => $currentUserId,
                    'evenement_id' => $evenement->getId()
                ]);

                // 6. Exécution de la requête avec try-catch
                try {
                    $donateurs = $query->getResult();
                    
                    $this->logger->debug('Donateurs trouvés', [
                        'nombre' => count($donateurs),
                        'details' => array_map(function($d) {
                            return [
                                'id' => $d['id'],
                                'email' => $d['email'],
                                'nom_complet' => $d['nom'] . ' ' . $d['prenom']
                            ];
                        }, $donateurs)
                    ]);

                    // 7. Envoi des emails
                    foreach ($donateurs as $donateur) {
                        $this->logger->debug('Préparation email pour donateur', [
                            'donateur_id' => $donateur['id'],
                            'donateur_email' => $donateur['email']
                        ]);

                        // Récupérer l'utilisateur complet pour le template
                        $donateurComplet = $entityManager->getRepository(Utilisateur::class)
                            ->find($donateur['id']);

                        if (!$donateurComplet) {
                            $this->logger->error('Donateur non trouvé en base', [
                                'id' => $donateur['id']
                            ]);
                            continue;
                        }

                        try {
                            $email = (new TemplatedEmail())
                                ->from('anasallam02@gmail.com')
                                ->to($donateur['email'])
                                ->subject('On y est presque ! Aidez-nous à atteindre l\'objectif')
                                ->htmlTemplate('emails/funding_progress.html.twig')
                                ->context([
                                    'evenement' => $evenement,
                                    'percentage' => round($percentage, 1),
                                    'remaining_amount' => round($objectif - $collected, 2),
                                    'donateur' => $donateurComplet
                                ]);

                            $transport = \Symfony\Component\Mailer\Transport::fromDsn($_ENV['MAILER_DSN']);
                            $mailer = new \Symfony\Component\Mailer\Mailer($transport);
                            $mailer->send($email);

                            $this->logger->debug('Email envoyé avec succès', [
                                'to' => $donateur['email']
                            ]);
                        } catch (\Exception $e) {
                            $this->logger->error('Erreur envoi email', [
                                'error' => $e->getMessage(),
                                'donateur_email' => $donateur['email'],
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Erreur lors de la récupération des donateurs', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                $this->logger->debug('Seuil non atteint', [
                    'percentage' => $percentage,
                    'seuil_requis' => '88%'
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur générale dans checkAndNotifyFundingProgress', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 