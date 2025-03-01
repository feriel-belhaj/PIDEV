<?php

namespace App\Controller;

use App\Entity\Creation;
use App\Form\CreationType;
use App\Repository\CreationRepository;
use App\Service\ProfanityFilterService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;       
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/creation', name: 'app_creation_')]
class CreationController extends AbstractController
{
    private $uploadDir;
    private $profanityFilter;
    private $security;

    public function __construct(string $uploadDir = 'uploads/images', ProfanityFilterService $profanityFilter = null, Security $security = null)
    {
        $this->uploadDir = $uploadDir;
        $this->profanityFilter = $profanityFilter;
        $this->security = $security;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CreationRepository $creationRepository): Response
    {
        return $this->render('creation/index.html.twig', [
            'creations' => $creationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request, ManagerRegistry $doctrine, ProfanityFilterService $profanityFilter, Security $security): Response
    {
        $creation = new Creation();
        
        // Set the current user as the creation author
        $currentUser = $security->getUser();
        if (!$currentUser) {
            $this->addFlash('error', 'Vous devez être connecté pour créer du contenu.');
            return $this->redirectToRoute('app_login');
        }
        
        $creation->setUtilisateur($currentUser);
        
        $form = $this->createForm(CreationType::class, $creation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Filter profanity from title and description
            $filteredTitle = $profanityFilter->filterText($creation->getTitre());
            $filteredDescription = $profanityFilter->filterText($creation->getDescription());
            $creation->setTitre($filteredTitle);
            $creation->setDescription($filteredDescription);
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9]/', '_', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                // Verify MIME type
                $mimeType = $imageFile->getMimeType();
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (!in_array($mimeType, $allowedTypes)) {
                    $this->addFlash('error', 'Type de fichier non autorisé. Types acceptés : JPG, PNG, GIF');
                    return $this->redirectToRoute('app_creation_new');
                }

                try {
                    $uploadPath = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    $imageFile->move(
                        $uploadPath,
                        $newFilename
                    );
                    $creation->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image: ' . $e->getMessage());
                    return $this->redirectToRoute('app_creation_new');
                }
            }

            try {
                $em = $doctrine->getManager();
                $em->persist($creation);
                $em->flush();
                
                $this->addFlash('success', 'Création ajoutée avec succès');
                return $this->redirectToRoute('app_creation_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement: ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
    
        return $this->render('creation/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Creation $creation): Response
    {
        return $this->render('creation/show.html.twig', [
            'creation' => $creation,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request, Creation $creation, ManagerRegistry $doctrine, ProfanityFilterService $profanityFilter): Response
    {
        // Check if the current user is the owner of the creation
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            $this->addFlash('error', 'Vous devez être connecté pour modifier du contenu.');
            return $this->redirectToRoute('app_login');
        }
        
        if ($creation->getUtilisateur() !== $currentUser && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette création.');
            return $this->redirectToRoute('app_creation_index');
        }
        
        $form = $this->createForm(CreationType::class, $creation);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Filter profanity from title and description during edit
            $filteredTitle = $profanityFilter->filterText($creation->getTitre());
            $filteredDescription = $profanityFilter->filterText($creation->getDescription());
            $creation->setTitre($filteredTitle);
            $creation->setDescription($filteredDescription);
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9]/', '_', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                // Verify MIME type
                $mimeType = $imageFile->getMimeType();
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (!in_array($mimeType, $allowedTypes)) {
                    $this->addFlash('error', 'Type de fichier non autorisé. Types acceptés : JPG, PNG, GIF');
                    return $this->redirectToRoute('app_creation_edit', ['id' => $creation->getId()]);
                }

                try {
                    $uploadPath = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
                    
                    // Delete old image if exists
                    if ($creation->getImage()) {
                        $oldImagePath = $uploadPath . '/' . $creation->getImage();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    $imageFile->move(
                        $uploadPath,
                        $newFilename
                    );
                    
                    $creation->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image: ' . $e->getMessage());
                    return $this->redirectToRoute('app_creation_edit', ['id' => $creation->getId()]);
                }
            }

            try {
                $doctrine->getManager()->flush();
                $this->addFlash('success', 'Création modifiée avec succès');
                return $this->redirectToRoute('app_creation_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement: ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('creation/edit.html.twig', [
            'creation' => $creation,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Request $request, Creation $creation, ManagerRegistry $doctrine): Response
    {
        // Check if the current user is the owner of the creation
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer du contenu.');
            return $this->redirectToRoute('app_login');
        }
        
        if ($creation->getUtilisateur() !== $currentUser && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette création.');
            return $this->redirectToRoute('app_creation_index');
        }
        
        if ($this->isCsrfTokenValid('delete'.$creation->getId(), $request->request->get('_token'))) {
            try {
                if ($creation->getImage()) {
                    $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/images/' . $creation->getImage();
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }

                $em = $doctrine->getManager();
                $em->remove($creation);
                $em->flush();
                $this->addFlash('success', 'Création supprimée avec succès');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_creation_index');
    }
}