<?php

namespace App\Service;

use App\Entity\Commentaire;
use App\Entity\Creation;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailService
{
    private $mailer;
    private $urlGenerator;
    private $senderEmail;

    public function __construct(
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
        string $senderEmail = 'noreply@artizina.com'
    ) {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->senderEmail = $senderEmail;
    }

    /**
     * This method has been removed as the relationship with Utilisateur no longer exists
     * Keeping the method signature for backward compatibility but it's now a no-op
     * 
     * @deprecated
     */
    public function sendNewCommentNotification(): void
    {
        // No-op - relationship with Utilisateur has been removed
        return;
    }
    
    /**
     * Send notification to admin about new comment that might need moderation
     * 
     * @param Commentaire $commentaire The new comment
     * @return void
     */
    public function sendCommentModerationNotification(Commentaire $commentaire): void
    {
        $creation = $commentaire->getCreation();
        
        $subject = 'Nouveau commentaire à modérer';
        
        // Generate URL to admin comment moderation page
        $moderationUrl = $this->urlGenerator->generate('commentaire_edit', 
            ['id' => $commentaire->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        $content = $this->renderModerationNotificationTemplate(
            $creation->getTitre(),
            $commentaire->getContenu(),
            $moderationUrl
        );
        
        $email = (new Email())
            ->from(new Address($this->senderEmail, 'Artizina'))
            ->to('test@localhost.com') // Changed from admin@artizina.com to a local test email
            ->subject($subject)
            ->html($content);
            
        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log the error but don't throw it to prevent disrupting the user experience
            error_log('Failed to send email: ' . $e->getMessage());
        }
    }
    
    /**
     * Render HTML template for comment notification
     */
    private function renderCommentNotificationTemplate(
        string $ownerName,
        string $creationTitle,
        string $commentContent,
        string $creationUrl
    ): string {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4A6572; color: white; padding: 10px 20px; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { font-size: 12px; color: #777; padding: 10px 20px; text-align: center; }
                .button { display: inline-block; padding: 10px 20px; background-color: #F9AA33; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Nouveau commentaire sur votre création</h2>
                </div>
                <div class="content">
                    <p>Bonjour {$ownerName},</p>
                    <p>Un nouveau commentaire a été ajouté à votre création <strong>"{$creationTitle}"</strong>.</p>
                    <p><em>"{$commentContent}"</em></p>
                    <p>
                        <a href="{$creationUrl}" class="button">Voir le commentaire</a>
                    </p>
                </div>
                <div class="footer">
                    <p>Ceci est un message automatique, merci de ne pas y répondre.</p>
                    <p>&copy; Artizina - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
    
    /**
     * Render HTML template for moderation notification
     */
    private function renderModerationNotificationTemplate(
        string $creationTitle,
        string $commentContent,
        string $moderationUrl
    ): string {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4A6572; color: white; padding: 10px 20px; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .comment { background-color: #fff; padding: 15px; border-left: 4px solid #F9AA33; margin: 10px 0; }
                .footer { font-size: 12px; color: #777; padding: 10px 20px; text-align: center; }
                .button { display: inline-block; padding: 10px 20px; background-color: #F9AA33; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Nouveau commentaire à modérer</h2>
                </div>
                <div class="content">
                    <p>Un nouveau commentaire a été ajouté à la création <strong>"{$creationTitle}"</strong> et pourrait nécessiter une modération.</p>
                    <div class="comment">
                        <p><em>"{$commentContent}"</em></p>
                    </div>
                    <p>
                        <a href="{$moderationUrl}" class="button">Modérer le commentaire</a>
                    </p>
                </div>
                <div class="footer">
                    <p>Ceci est un message automatique, merci de ne pas y répondre.</p>
                    <p>&copy; Artizina - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}