<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;


#[Route('/formation')]
final class FormationController extends AbstractController
{
    private $imagesDirectory;

    public function __construct(ParameterBagInterface $params)
    {
        // Fetch the image directory parameter
        $this->imagesDirectory = $params->get('formation_images_directory');

        // Créer le dossier d'upload s'il n'existe pas
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->imagesDirectory)) {
            $filesystem->mkdir($this->imagesDirectory);
        }
    }




    #[Route(name: 'app_formation_index', methods: ['GET'])]
    public function index(FormationRepository $formationRepository): Response
    {
        return $this->render('formation/index.html.twig', [
            'formations' => $formationRepository->findAll(),
        ]);
    }
   
    #[Route('/formations', name: 'app_formation_index1', methods: ['GET'])]
public function index1(FormationRepository $formationRepository): Response
{
    return $this->render('formation/index1.html.twig', [
        'formations' => $formationRepository->findAll(),
    ]);
}

    #[Route('/new', name: 'app_formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('formation_images_directory'),
                        $newFilename
                    );
                    $formation->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                }
            }

            $entityManager->persist($formation);
            $entityManager->flush();

            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_formation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                $oldImage = $formation->getImage();
                if ($oldImage) {
                    $oldImagePath = $this->getParameter('formation_images_directory').'/'.$oldImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('formation_images_directory'),
                        $newFilename
                    );
                    $formation->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                }
            }
            // Si pas de nouvelle image, on garde l'ancienne

            $entityManager->flush();
            return $this->redirectToRoute('app_formation_index1', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/edit.html.twig', [
            'formation' => $formation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($formation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/formation/{id}/add-to-cart', name: 'app_formation_add_to_cart', methods: ['POST'])]
    public function addToCart(
        int $id, 
        Request $request, 
        CsrfTokenManagerInterface $csrfTokenManager, 
        SessionInterface $session
    ) {
        // Vérifier le token CSRF
        $token = $request->request->get('_csrf_token');
    
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('add_to_cart', $token))) {
            throw new AccessDeniedException('Invalid CSRF token');
        }
    
        // Ajouter au panier
        $cart = $session->get('cart', []);
        $cart[] = $id;
        $session->set('cart', $cart);
    
        return $this->redirectToRoute('app_formation_index');
    }
    #[Route('/cart', name: 'app_formation_cart', methods: ['GET'])]

    public function showCart(Request $request, FormationRepository $formationRepository): Response
    {
        // Récupérer la session
        $session = $request->getSession();

        // Récupérer le panier depuis la session
        $cart = $session->get('cart', []);

        // Récupérer les formations correspondantes aux IDs du panier
        $formations = $formationRepository->findBy(['id' => $cart]);

        // Afficher la page du panier
        return $this->render('formation/cart.html.twig', [
            'formations' => $formations,
        ]);
    }
    


}
