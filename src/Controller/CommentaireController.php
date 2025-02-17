<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;



final class CommentaireController extends AbstractController
{
    #[Route(name: 'app_commentaire_index', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
        ]);
    }

    #[Route('/showcmnt', name: 'showcmnt')]
    public function showcmnt (CommentaireRepository $serRep ): Response
    {
        $Blog = $serRep->findAll();
        return $this->render('commentaire/show.html.twig', [
            'tabservice' => $Blog,
        ]);
    }  

    #[Route('/addFromcmnt', name: 'addFromcmnt')]
    public function addFromcmnt( ManagerRegistry $m, Request $req): Response
    {
        $em = $m->getManager(); 
        $serv = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $serv);
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($serv);
            $em->flush();
            return $this->redirectToRoute('showcmnt'); 
        }
    
        return $this->render('commentaire/addForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/updateFormcmnt/{id}', name: 'updateFormcmn')]
    public function updateFormcmnt(ManagerRegistry $m, Request $req, $id, CommentaireRepository $BlogRep): Response
    {

        $em = $m->getManager(); 
        $Blog = $BlogRep->find($id);
        $form=$this->createForm(CommentaireType::class, $Blog);
        $form->handleRequest($req);
        
        if ($form->isSubmitted() && $form->isValid()){
            $em->persist($Blog);
            $em->flush();
            return $this->redirectToRoute('showcmnt'); 

        }
        return $this->render('commentaire/modForm.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/deleteFormBlog/{id}', name: 'deleteFormBlog')]
    public function deleteFormBlog( $id,ManagerRegistry $m, CommentaireRepository $BlogRep): Response
    {
        $em = $m->getManager();
    
        $Blog = $BlogRep->find($id);
      
        $em->remove($Blog);
        $em->flush();
        return $this->redirectToRoute('showcmnt'); // redige vers la liste des auteurs aprés l'ajout  

    }
}
