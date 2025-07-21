<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use App\Entity\Notification;
use App\Entity\User;

class NotificationEmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private ParameterBagInterface $parameterBag
    ) {}

    public function sendNotificationEmail(Notification $notification, User $user): void
    {
        // Obtenir les paramètres de configuration
        $fromEmail = $_ENV['MAILER_FROM_EMAIL'] ?? 'noreply@tandem.com';
        $fromName = $_ENV['MAILER_FROM_NAME'] ?? 'Tandem';

        // Créer l'email principal pour l'utilisateur
        $email = (new Email())
            ->from($fromEmail)
            ->to($user->getEmail())
            ->subject($this->getEmailSubject($notification))
            ->html($this->renderEmailTemplate($notification, $user));

        // Ajouter une copie à votre email personnel si différent
        if ($fromEmail !== $user->getEmail()) {
            $email->cc($fromEmail);
        }

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas faire échouer le processus
            error_log('Erreur envoi email: ' . $e->getMessage());
        }
    }

    private function getEmailSubject(Notification $notification): string
    {
        return match($notification->getType()) {
            'interview' => '📅 Entretien programmé - ' . $notification->getTitle(),
            'reminder' => '⏰ Rappel - ' . $notification->getTitle(),
            'info' => '📢 ' . $notification->getTitle(),
            'warning' => '⚠️ ' . $notification->getTitle(),
            'success' => '✅ ' . $notification->getTitle(),
            default => '📧 ' . $notification->getTitle()
        };
    }

    private function renderEmailTemplate(Notification $notification, User $user): string
    {
        return $this->twig->render('emails/notification.html.twig', [
            'notification' => $notification,
            'user' => $user,
            'frontendUrl' => $_ENV['FRONTEND_URL'] ?? 'http://tandems.social'
        ]);
    }
}
