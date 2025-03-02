<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailController extends AbstractController
{
    #[Route('/send-email', name: 'send_email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        
        $email = (new Email())
            ->from('kridtaoufik994@gmail.com') // Expéditeur
            ->to('taoufik.krid.949@gmail.com') // Destinataire
            ->subject('Confirmation de commande')
            ->text('Votre commande a été traitée avec succès !')
            ->html('<p>Votre commande a été traitée avec succès ! Merci pour votre achat.</p>');

        $mailer->send($email);

        return new Response('✅ Email envoyé avec succès ! Vérifie ta boîte Gmail.');
    }
}
