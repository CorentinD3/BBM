<?php

namespace App\Controller;

use App\Entity\Hours;
use App\Entity\OldAppointment;
use App\Repository\HoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

class MailController extends AbstractController
{

    #[Route('/test-email', name: 'test_email')]
    #[IsGranted('ROLE_ADMIN')]
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        // Contenu minimal du mail pour le test
        $htmlContent = '<p>Test d\'envoi d\'e-mail</p>';

        try {
            $email = (new Email())
                ->from(new Address('noreply@beauty-bym.fr'))
                ->to(new Address('bogaert.corentin@hotmail.fr'))
                ->subject('🔔 Confirmation de votre rendez-vous - Beauty By M')
                ->text('Votre rendez-vous est confirmé chez Beauty By M.')
                ->html($htmlContent);

            // Ajout d'un header personnalisé (optionnel)
            $email->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:noreply@beauty-bym.fr?subject=unsubscribe>');

            // Création d'une enveloppe (optionnelle)
            $envelope = new Envelope(
                new Address('noreply@beauty-bym.fr'),
                [new Address('bogaert.corentin@hotmail.fr')]
            );

            // Envoi du mail
            $mailer->send($email, $envelope);

            return new Response('✅ E-mail envoyé avec succès !');
        } catch (\Exception $e) {
            return new Response('❌ Erreur d\'envoi : ' . $e->getMessage(), 500);
        }
    }


    #[Route('/email', name: 'email')]
    #[IsGranted('ROLE_ADMIN')]
    public function email(Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login'); // Redirige si non connecté
        }

        $appointment = $entityManager->getRepository(Hours::class)->findOneBy([
            'user' => $user
        ]);

        if (!$appointment) {
            $this->addFlash('error', 'Aucun rendez-vous trouvé.');
            return $this->redirectToRoute('app_home');
        }

        // Récupération de la date et de l'heure
        $dateObj = $appointment->getDate()->getDate();
        $hourObj = $appointment->getHour();

        // Combinaison date + heure dans un seul \DateTime
        $appointmentDateTime = new \DateTime(
            $dateObj->format('Y-m-d') . ' ' . $hourObj->format('H:i:s')
        );

        // Calcul de la limite d'annulation (48h avant)
        $cancelLimitDate = (clone $appointmentDateTime)->modify('-48 hours');

        // Formatage de la date pour l'affichage (en français)
        setlocale(LC_TIME, 'fr_FR.UTF-8');
        $dateFormatted = strftime('%A %d %B %Y', $dateObj->getTimestamp());

        // Récupération des informations de prestation/variant
        $prestation = $appointment->getPrestation();
        $variant = $appointment->getVariant();

        // Construire eventTitle en se basant sur la prestation ou variant
        if ($variant) {
            $eventTitle = "Rendez-vous " . $variant->getLabel();
        } elseif ($prestation) {
            $eventTitle = "Rendez-vous " . $prestation->getLabel();
        } else {
            $eventTitle = "Rendez-vous";
        }
        $appointmentDateTime = new \DateTime(
            $dateObj->format('Y-m-d') . ' ' . $hourObj->format('H:i:s'),
            new \DateTimeZone('Europe/Paris')
        );
        // Conversion en UTC pour le format ICS (Google Calendar attend des dates en UTC)
        $startDateUtc = (clone $appointmentDateTime)->setTimezone(new \DateTimeZone('UTC'));
        $eventDtstart = $startDateUtc->format('Ymd\THis\Z');

        // Déterminer la durée
        if ($variant) {
            $duration = $variant->getDuration();
        } elseif ($prestation) {
            $duration = $prestation->getDuration();
        } else {
            $duration = null;
        }

        if ($duration instanceof \DateTime) {
            // Convertir le DateTime en DateInterval
            $hours = (int)$duration->format('H');
            $minutes = (int)$duration->format('i');
            $seconds = (int)$duration->format('s');
            $durationInterval = new \DateInterval("PT{$hours}H{$minutes}M{$seconds}S");
        } elseif ($duration instanceof \DateInterval) {
            $durationInterval = $duration;
        } else {
            $durationInterval = new \DateInterval('PT0H0M0S');
        }

        $endDateUtc = (clone $startDateUtc)->add($durationInterval);
        $eventDtend = $endDateUtc->format('Ymd\THis\Z');

        // Récupérer le token d'annulation
        // On suppose qu'il est stocké dans le champ cancelToken de l'entité Hours
        $cancelToken = $appointment->getCancelToken();

        $dataToView = [
            'user'          => $user->getUserProfile(),
            'number'        => $user->getNumber(),
            'prestation'    => $variant ?$prestation->getLabel() .' / '. $variant->getLabel() : ($prestation ? $prestation->getLabel() : 'Prestation inconnue'),
            'duration'      => $variant ? $variant->getDuration()->format('H:i') : ($prestation ? $prestation->getDuration()->format('H:i') : '00:00'),
            'hour'          => $hourObj->format('H:i'),
            'date'          => $dateFormatted,
            'price'         => $variant ? $variant->getPrice() : ($prestation ? $prestation->getPrice() : 0),
            'cancelLimit'   => $cancelLimitDate,
            'eventLocation' => 'Beauty By M, 81 Rue Albert Cuenin, Dunkerque, France',
            'cancelToken'   => $cancelToken,
            'eventDtstart'  => $eventDtstart,
            'eventDtend'    => $eventDtend,
            'eventTitle'    => $eventTitle,
            'eventDescription' => "Rendez-vous Beauty By M" // Vous pouvez modifier ou dynamiser ce champ
        ];

        return $this->render('email/test_email.html.twig', [
            'reservation' => $dataToView,
        ]);
    }


    #[Route('/cancel-appointment/offline', name: 'app_cancel_appointment_offline')]
    public function cancelAppointmentOffline(
        Request $request,
        EntityManagerInterface $entityManager,
        HoursRepository $hoursRepository
    ): Response {
        $token = $request->query->get('token');

        if (!$token) {
            $this->addFlash('error', 'Token manquant.');
            return $this->redirectToRoute('app_home');
        }

        // Rechercher le rendez-vous correspondant au token
        $hourEntity = $hoursRepository->findOneBy(['cancelToken' => $token]);
        if (!$hourEntity) {
            $this->addFlash('error', 'Réservation introuvable ou déjà annulée.');
            return $this->redirectToRoute('app_home');
        }

        $appointmentDate = $hourEntity->getDate()->getDate();
        $appointmentTime = $hourEntity->getHour();
        $appointmentDateTime = new \DateTime($appointmentDate->format('Y-m-d') . ' ' . $appointmentTime->format('H:i:s'));

        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));

        $diffInSeconds = $appointmentDateTime->getTimestamp() - $now->getTimestamp();
        $diffInHours = $diffInSeconds / 3600;

        if ($diffInHours < 48) {
            $this->addFlash('error', 'Il n’est plus possible d’annuler votre réservation à moins de 48 heures. Veuillez nous contacter si besoin.');
            return $this->redirectToRoute('app_home');
        }

        // Récupérer l’utilisateur associé au rendez-vous (si besoin)
        $currentUser = $hourEntity->getUser();
        // ou $currentUser = $hourEntity->getUser() ?? null;

        // Créer une copie dans OldAppointment
        $oldAppointment = new OldAppointment();
        if ($currentUser) {
            $oldAppointment->setUser($currentUser);
        }
        $oldAppointment->setHour($hourEntity->getHour());
        $oldAppointment->setDate($hourEntity->getDate()->getDate());
        $oldAppointment->setPrestation($hourEntity->getPrestation()?->getLabel());
        if ($hourEntity->getVariant()) {
            $oldAppointment->setVariant($hourEntity->getVariant()->getLabel());
        }
        $oldAppointment->setStatus("canceled");

        // Annuler la réservation actuelle
        $hourEntity->setUser(null);
        $hourEntity->setPrestation(null);
        $hourEntity->setVariant(null);
        // Optionnel : vider le token pour éviter une double-annulation
        $hourEntity->setCancelToken(null);

        $entityManager->persist($oldAppointment);
        $entityManager->persist($hourEntity);
        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a bien été annulée (via email).');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/cancel-appointment/check', name: 'app_cancel_appointment_check')]
    public function cancelAppointmentCheck(
        Request $request,
        HoursRepository $hoursRepository
    ): Response {
        $token = $request->query->get('token');

        if (!$token) {
            $this->addFlash('error', 'Token manquant.');
            return $this->redirectToRoute('app_home');
        }

        // On récupère le rendez-vous correspondant au token
        $hourEntity = $hoursRepository->findOneBy(['cancelToken' => $token]);
        if (!$hourEntity) {
            $this->addFlash('error', 'Aucun rendez-vous trouvé ou déjà annulé.');
            return $this->redirectToRoute('app_home');
        }

        // On affiche une page “confirmation”, en passant le token
        return $this->render('email/confirm.html.twig', [
            'token' => $token,
            'hour'  => $hourEntity,
        ]);
    }

    #[Route('/redirect-maps', name: 'app_redirect_maps')]
    public function redirectMaps(Request $request): Response
    {
        // On récupère la localisation demandée (ex: bergues)
        $location = $request->query->get('location', 'bergues');

        // On prépare les adresses, par ex. selon $location
        // => Dans ton cas, tu peux aussi préparer un mapping PHP
        $appleMapsLocations = [
            'bergues' => 'Beauty+By+M+81+Rue+Albert+Cuenin,+Dunkerque,+France',
        ];
        $googleMapsLocations = [
            'bergues' => 'Beauty+By+M+81+Rue+Albert+Cuenin,+Dunkerque,+France',
        ];

        // On vérifie si la clé $location existe
        $appleQuery = $appleMapsLocations[$location] ?? 'Beauty+By+M';
        $googleQuery = $googleMapsLocations[$location] ?? 'Beauty+By+M';

        // On récupère le user-agent
        $userAgent = $request->headers->get('User-Agent', '');

        // Détection simplifiée
        if (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            // iOS => Apple Maps
            $mapsUrl = "https://maps.apple.com/?q=" . urlencode($appleQuery);
        } else {
            // Sinon => Google Maps
            $mapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($googleQuery);
        }

        // Redirection
        return $this->redirect($mapsUrl);
    }

    #[Route('/calendar-redirect', name: 'app_calendar_redirect')]
    public function calendarRedirect(Request $request): Response
    {
        // 1) Récupérer les paramètres depuis l’URL
        $start = $request->query->get('start'); // ex: '20250328T140000Z'
        $end = $request->query->get('end');     // ex: '20250328T150000Z'
        $title = $request->query->get('title', 'Mon RDV');
        $description = $request->query->get('description', '');
        $location = $request->query->get('location', '');

        // 2) Construire l’URL Google Calendar
        //    (on URL-encode pour éviter les problèmes d’espaces)
        $googleCalUrl = sprintf(
            'https://www.google.com/calendar/render?action=TEMPLATE&text=%s&dates=%s/%s&details=%s&location=%s',
            urlencode($title),
            $start,
            $end,
            urlencode($description),
            urlencode($location)
        );

        // 3) Générer l’URL vers le .ics (autre route)
        //    (cf. la route "download_appointment" ci-dessous)
        $icsUrl = $this->generateUrl('download_appointment', [
            'start' => $start,
            'end' => $end,
            'title' => $title,
            'description' => $description,
            'location' => $location
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        // 4) Détecter si c’est iOS (User-Agent)
        $userAgent = $request->headers->get('User-Agent', '');
        if (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            // iOS => on redirige vers l’ICS
            return $this->redirect($icsUrl);
        } else {
            // Sinon => Google Calendar
            return $this->redirect($googleCalUrl);
        }
    }

    #[Route('/download-appointment', name: 'download_appointment')]
    public function downloadAppointment(Request $request): Response
    {
        $start = $request->query->get('start'); // ex: "20250328T140000Z"
        $end = $request->query->get('end');     // ex: "20250328T150000Z"
        $title = $request->query->get('title', 'Mon RDV');
        $description = $request->query->get('description', '');
        $location = $request->query->get('location', '');

        // Contenu ICS
        $icsContent = "BEGIN:VCALENDAR\r\n".
            "VERSION:2.0\r\n".
            "BEGIN:VEVENT\r\n".
            "UID:".uniqid()."\r\n".
            "DTSTAMP:".gmdate('Ymd\THis\Z')."\r\n".
            "DTSTART:".$start."\r\n".
            "DTEND:".$end."\r\n".
            "SUMMARY:".$title."\r\n".
            "DESCRIPTION:".$description."\r\n".
            "LOCATION:".$location."\r\n".
            "END:VEVENT\r\n".
            "END:VCALENDAR";

        // Réponse HTTP avec headers appropriés
        $response = new Response($icsContent);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="event.ics"');
        return $response;
    }
}