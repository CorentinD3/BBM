<?php
// src/Service/EmailSenderService.php
namespace App\Service;

use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Envelope;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class EmailSenderService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, Environment $twig, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->twig   = $twig;
        $this->logger = $logger;
    }

    /**
     * Envoie l'e-mail de confirmation de rendez-vous.
     *
     * @param array  $dataToView    Les données à passer au template Twig.
     * @param string $recipientEmail L'adresse e-mail du destinataire.
     *
     * @throws Exception|TransportExceptionInterface En cas d'erreur d'envoi.
     */
    public function sendConfirmationEmail(array $dataToView, string $recipientEmail): void
    {
        // Rendu du template Twig avec les données
        $htmlContent = $this->twig->render('email/test_email.html.twig', [
            'reservation' => $dataToView,
        ]);

        try {
            $email = (new Email())
                ->from(new Address('noreply@beauty-bym.fr'))
                ->to(new Address($recipientEmail))
                ->subject('🔔 Confirmation de votre rendez-vous - Beauty By M')
                ->text('Votre rendez-vous est confirmé chez Beauty By M.')
                ->html($htmlContent);

            // Exemple d'ajout d'un header personnalisé
            $email->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:noreply@beauty-bym.fr?subject=unsubscribe>');

            // Création d'une enveloppe (optionnelle, si vous souhaitez spécifier des adresses séparées)
            $envelope = new Envelope(
                new Address('noreply@beauty-bym.fr'),
                [new Address($recipientEmail)]
            );

            $this->mailer->send($email, $envelope);
            $this->logger->info("E-mail de confirmation envoyé à {$recipientEmail}.");
        } catch (Exception $e) {
            $this->logger->error("Erreur lors de l'envoi de l'e-mail de confirmation : " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Envoie l'e-mail de confirmation de rendez-vous.
     *
     * @param array  $dataToView    Les données à passer au template Twig.
     * @param string $recipientEmail L'adresse e-mail du destinataire.
     *
     * @throws Exception|TransportExceptionInterface En cas d'erreur d'envoi.
     */
    public function sendConfirmationEmailAdmin(array $dataToView, string $recipientEmail): void
    {
        // Rendu du template Twig avec les données
        $htmlContent = $this->twig->render('email/emailAdmin.html.twig', [
            'reservation' => $dataToView,
        ]);

        try {
            $email = (new Email())
                ->from(new Address('noreply@beauty-bym.fr'))
                ->to(new Address($recipientEmail))
                ->subject('🔔 Nouveau rendez-vous confirmé')
                ->text('Un rendez-vous est confirmé chez Beauty By M.')
                ->html($htmlContent);

            // Exemple d'ajout d'un header personnalisé
            $email->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:noreply@beauty-bym.fr?subject=unsubscribe>');

            // Création d'une enveloppe (optionnelle, si vous souhaitez spécifier des adresses séparées)
            $envelope = new Envelope(
                new Address('noreply@beauty-bym.fr'),
                [new Address($recipientEmail)]
            );

            $this->mailer->send($email, $envelope);
            $this->logger->info("E-mail de confirmation envoyé à {$recipientEmail}.");
        } catch (Exception $e) {
            $this->logger->error("Erreur lors de l'envoi de l'e-mail de confirmation : " . $e->getMessage());
            throw $e;
        }
    }
}
