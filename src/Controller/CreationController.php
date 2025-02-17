<?php

namespace App\Controller;

use App\Entity\Creation;
use App\Form\CreationType;
use App\Repository\CreationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;       
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class CreationController extends AbstractController{
    #[Route('/craetion', name: 'app_creation')]
    public function index(): Response
    {
        return $this->render('creation/index.html.twig', [
            'controller_name' => 'ServiceController',
        ]);
    }

    #[Route('/showcreation', name: 'showcreation')]
    public function showservice (CreationRepository $serRep ): Response
    {
        $Blog = $serRep->findAll();
        return $this->render('creation/show.html.twig', [
            'tabservice' => $Blog,
        ]);
    }  

    #[Route('/addFormcreation', name: 'addFormcreation')]
    public function addFromcraetion( ManagerRegistry $m, Request $req): Response
    {
        $em = $m->getManager(); 
        $serv = new Creation();
        $form = $this->createForm(CreationType::class, $serv);
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($serv);
            $em->flush();
            return $this->redirectToRoute('showcreation'); 
        }
    
        return $this->render('creation/addForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/updateFormservice/{id}', name: 'updateFormservice')]
    public function updateFormBlog(ManagerRegistry $m, Request $req, $id, CreationRepository $BlogRep): Response
    {

        $em = $m->getManager(); 
        $Blog = $BlogRep->find($id);
        $form=$this->createForm(CreationType::class, $Blog);
        $form->handleRequest($req);
        
        if ($form->isSubmitted() && $form->isValid()){
            $em->persist($Blog);
            $em->flush();
            return $this->redirectToRoute('showcreation'); 

        }
        return $this->render('creation/modForm.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/deletcreation/{id}', name: 'deletecreation')]
    public function deleteFormBlog( $id,ManagerRegistry $m, CreationRepository $BlogRep): Response
    {
        $em = $m->getManager();
    
        $Blog = $BlogRep->find($id);
      
        $em->remove($Blog);
        $em->flush();
        return $this->redirectToRoute('showcreation'); // redige vers la liste des auteurs aprés l'ajout  

    }


}