<?php

namespace App\Controller;

use App\Entity\Don;
use App\Entity\Evenement;
use App\Entity\Formation;
use App\Entity\Utilisateur;
// Ajout des use pour Stripe
require_once __DIR__ . '/../../vendor/stripe/stripe-php/init.php';
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
use Doctrine\ORM\EntityManagerInterface;

//
//use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

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
            $don->setUser($user); 
            $don->setCreateur($user);// Important : définir l'utilisateur

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

                /** @var Utilisateur $currentUser */

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

    #rawen
    #[Route('/stripe/checkout/{id}', name: 'app_stripe_checkout')]
    public function checkoutForm(Formation $formation): Response
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
    public function successForm(): Response
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

        return $this->render('stripe/successFormation.html.twig', [
            'debug' => [
                'user_email' => $userEmail ?? 'non défini',
                'mailer_dsn' => $dsn,
                'php_version' => PHP_VERSION,
                'symfony_env' => $_ENV['APP_ENV']
            ]
        ]);
    }
    #[Route('/stripe/cancel', name: 'app_stripe_cancel')]
    public function cancelForm(): Response
    {
        return $this->render('stripe/cancelFormation.html.twig');
    }
}
