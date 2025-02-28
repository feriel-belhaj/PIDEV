<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Utilisateur;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Service\GeminiAiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Don;

#[Route('/evenement')]
final class EvenementController extends AbstractController
{
    #[Route(name: 'app_evenement_index', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $evenementRepository): Response
    {
        $categorie = $request->query->get('categorie');
        $tri = $request->query->get('tri', 'date_creation');
        $recherche = $request->query->get('recherche');
        $page = $request->query->getInt('page', 1);
        $limit = 6; // Nombre d'événements par page
        
        $evenements = $evenementRepository->findByFiltersWithPagination(
            $categorie,
            $tri,
            $recherche,
            $page,
            $limit
        );

        $totalEvenements = $evenementRepository->countTotal($categorie, $recherche);
        $totalPages = \ceil($totalEvenements / $limit);

        // Liste des catégories disponibles
        $categories = [
            'Toutes les catégories',
            'Musique',
            'Théâtre',
            'Humour',
            'Danse',
            'Peinture & Arts visuels',
            'Cinéma & Audiovisuel',
            'Artisanat',
            'Littérature & Poésie',
            'Mode & Design'
        ];

        return $this->render('evenement/event.html.twig', [
            'evenements' => $evenements,
            'categories' => $categories,
            'selectedCategorie' => $categorie ?? 'Toutes les catégories',
            'selectedTri' => $tri,
            'recherche' => $recherche,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'limit' => $limit
        ]);
    }

    #[Route('/admin', name: 'app_evenement_admin', methods: ['GET'])]
    public function adminIndex(EvenementRepository $evenementRepository): Response
    {
        return $this->render('evenement/evenements.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $evenement->setCreatedat(new \DateTimeImmutable());
        $evenement->setCollectedamount(0);
        $evenement->setStatus('actif');
        $evenement->setCategorie('Musique');
        
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $evenement->setImageurl($newFilename);
                } catch (FileException $e) {
                    // Gérer l'erreur si nécessaire
                }
            }
            $user = $security->getUser();

            if ($user instanceof Utilisateur) {             
                $evenement->addUtilisateur($user);
            }
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/test-gemini-api', name: 'app_test_gemini_api', methods: ['GET'])]
    public function testGeminiApi(HttpClientInterface $httpClient, LoggerInterface $logger): JsonResponse
    {
        $apiKey = $this->getParameter('gemini_api_key');
        $logger->info('Testing Gemini API connection with key: ' . substr($apiKey, 0, 5) . '...');
        
        // Updated endpoints for Google AI Studio - prioritize the working endpoint
        $endpoints = [
            // This endpoint is confirmed to be working
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent",
            // Fallback endpoints
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent",
            "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent",
            "https://us-central1-aiplatform.googleapis.com/v1/projects/PROJECT_ID/locations/us-central1/publishers/google/models/gemini-pro:generateContent"
        ];
        
        $results = [];
        
        foreach ($endpoints as $baseUrl) {
            $url = "{$baseUrl}?key={$apiKey}";
            
            // Special handling for Vertex AI endpoint
            if (strpos($baseUrl, 'aiplatform.googleapis.com') !== false) {
                // Replace PROJECT_ID with a placeholder - the API key should work regardless
                $url = str_replace('PROJECT_ID', 'gemini-api-project', $url);
            }
            
            try {
                $logger->debug('Testing endpoint: ' . $baseUrl);
                
                // Determine if we're using Vertex AI or regular Gemini API
                $isVertexAi = strpos($baseUrl, 'aiplatform.googleapis.com') !== false;
                
                // Prepare the appropriate payload
                $payload = $isVertexAi ? [
                    'instances' => [
                        [
                            'prompt' => 'Hello, please respond with a simple "Hello, world!"'
                        ]
                    ],
                    'parameters' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 10
                    ]
                ] : [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => 'Hello, please respond with a simple "Hello, world!"'
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 10
                    ]
                ];
                
                $response = $httpClient->request('POST', $url, [
                    'json' => $payload,
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ]
                ]);
                
                $statusCode = $response->getStatusCode();
                $content = $response->getContent(false);
                
                $results[$baseUrl] = [
                    'status' => $statusCode,
                    'success' => $statusCode === 200,
                    'response' => substr($content, 0, 200) . '...'
                ];
                
                if ($statusCode === 200) {
                    $logger->info('Successfully connected to Gemini API using endpoint: ' . $baseUrl);
                    // If we get a successful response, we can stop testing
                    break;
                }
            } catch (\Exception $e) {
                $logger->error('Error testing endpoint ' . $baseUrl . ': ' . $e->getMessage());
                $results[$baseUrl] = [
                    'status' => 'error',
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return new JsonResponse([
            'api_key_length' => strlen($apiKey),
            'api_key_prefix' => substr($apiKey, 0, 5) . '...',
            'endpoints_tested' => count($results),
            'results' => $results
        ]);
    }

    #[Route('/generate-ai-content', name: 'app_evenement_generate_ai', methods: ['POST'])]
    public function generateAiContent(Request $request, GeminiAiService $geminiAiService, LoggerInterface $logger): JsonResponse
    {
        $prompt = $request->request->get('prompt');
        
        if (empty($prompt)) {
            return new JsonResponse(['error' => 'Prompt cannot be empty'], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $projectDetails = $geminiAiService->generateProjectDetails($prompt);
            
            if (isset($projectDetails['error'])) {
                $logger->error('Gemini API error: ' . $projectDetails['error']);
                return new JsonResponse(['error' => $projectDetails['error']], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            
            return new JsonResponse($projectDetails);
        } catch (\Exception $e) {
            $logger->error('Exception in generateAiContent: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Error generating content: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        // Mise à jour du statut si nécessaire
        if ($evenement->isTermine() && $evenement->getStatus() !== 'termine') {
            $evenement->updateStatus();
            $entityManager->flush();
        }

        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenement_admin', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin/evenements/dashboard', name: 'app_events_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $evenementRepo = $entityManager->getRepository(Evenement::class);
        $donRepo = $entityManager->getRepository(Don::class);

        // Statistiques de base
        $totalEvenements = $evenementRepo->count([]);
        $totalDons = $donRepo->createQueryBuilder('d')
            ->select('SUM(d.amount)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
        
        $totalDonateurs = $donRepo->createQueryBuilder('d')
            ->select('COUNT(DISTINCT d.id)')
            ->getQuery()
            ->getSingleScalarResult();
        
        $nombreDons = $donRepo->count([]);

        // Évolution des dons par mois
        $donsMensuels = $donRepo->createQueryBuilder('d')
            ->select('SUBSTRING(d.donationdate, 6, 2) as mois, SUM(d.amount) as total, COUNT(d.id) as nombre')
            ->groupBy('mois')
            ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getResult();

        // Statistiques par catégorie d'événement
        $statsParCategorie = $evenementRepo->createQueryBuilder('e')
            ->select('e.categorie, COUNT(e.id) as nombre, SUM(e.goalamount) as objectif, SUM(e.collectedamount) as collecte')
            ->groupBy('e.categorie')
            ->getQuery()
            ->getResult();

        // Derniers événements
        $derniersEvenements = $evenementRepo->createQueryBuilder('e')
            ->orderBy('e.createdat', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Préparation des données pour les graphiques
        $donsByMonth = array_fill(1, 12, ['total' => 0, 'nombre' => 0]);
        foreach ($donsMensuels as $don) {
            $mois = intval($don['mois']);
            $donsByMonth[$mois] = [
                'total' => floatval($don['total']),
                'nombre' => intval($don['nombre'])
            ];
        }

        return $this->render('evenement/eventsDashboard.html.twig', [
            'totalEvenements' => $totalEvenements,
            'totalDons' => $totalDons,
            'totalDonateurs' => $totalDonateurs,
            'nombreDons' => $nombreDons,
            'donsMensuels' => array_values($donsByMonth),
            'statsParCategorie' => $statsParCategorie,
            'derniersEvenements' => $derniersEvenements
        ]);
    }

    #[Route('/{id}/don', name: 'app_evenement_don', methods: ['GET', 'POST'])]
    public function don(Evenement $evenement): Response
    {
        // Vérifier si l'événement accepte encore les dons
        if ($evenement->isTermine()) {
            $this->addFlash('error', 'Cet événement est terminé et n\'accepte plus de dons.');
            return $this->redirectToRoute('app_evenement_show', ['id' => $evenement->getId()]);
        }

        // Rediriger vers la page de création de don
        return $this->redirectToRoute('app_don_new', ['id' => $evenement->getId()]);
    }
}
