<?php

namespace App\Controller;


use App\Entity\Date;
use App\Entity\Hours;
use App\Entity\User;
use App\Entity\OldAppointment;
use App\Entity\Variant;
use App\Form\UserProfileType;
use App\Repository\HoursRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'google_maps_api_key' => $_ENV['GOOGLE_MAPS_API_KEY']
        ]);
    }

    #[Route('/mixte', name: 'app_mixte')]
    public function mix(): Response
    {
        return $this->render('home/mixte.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/cil-a-cil', name: 'app_cil-a-cil')]
    public function cilACil(): Response
    {
        return $this->render('home/cilACil.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/volume-russe', name: 'app_volume-russe')]
    public function volRusse(): Response
    {
        return $this->render('home/volRusse.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/reservation', name: 'app_cal')]
    public function cal(): Response
    {
        $user = $this->getUser();
        if ($user instanceof User && $user->getBanList() !== null) {
            $this->addFlash('error', 'Votre compte est banni. Impossible de continuer.');
            return $this->redirectToRoute('app_logout');
        }

        return $this->render('home/cal.html.twig', [
            'controller_name' => 'CalController',
        ]);
    }

    #[Route('/rendez-vous', name: 'app_appointment')]
    public function appointment(Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login'); // Rediriger si non authentifié
        }

        $appointment = $entityManager->getRepository(Hours::class)->findOneBy([
            'user' => $user
        ]);

        $oldAppointments = $entityManager->getRepository(OldAppointment::class)->findBy([
            'user' => $user
        ]);

        return $this->render('home/userAppointment.html.twig', [
            "userAppointment" => $appointment,
            "userOldAppointment" => $oldAppointments,
        ]);
    }

    #[Route('/recap-page', name: 'app_recap-page')]
    public function recapPage(Request $request, EntityManagerInterface $entityManager, Security $security): Response {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Rediriger si non authentifié
        }

        $reservationDetails = $request->getSession()->get('reservation_details');

        if (!$reservationDetails) {
            $this->addFlash('error', 'Aucune réservation trouvée.');
            return $this->redirectToRoute('app_cal'); // Rediriger si aucune réservation
        }

        $hour = $reservationDetails['hour'];
        $date = $entityManager->getRepository(Date::class)->find($hour->getDate());
        $prestation = $reservationDetails['prestation'];
        $variantId = $reservationDetails['variant'] ?? null;

        $variant = null;
        if ($variantId) {
            $variant = $entityManager->getRepository(Variant::class)->find($variantId);
        }

        if ($variant) {
            $dataToView = [
                'user' => $user->getUserProfile(),
                'number' => $user->getNumber(),
                'prestation' => [
                    'label' => $variant->getPrestation()->getLabel(),
                    'variant' =>$variant->getLabel()],
                'duration' => $variant->getDuration(),
                'hour' => $hour->getHour()->format('H:i'),
                'date' => $date->getDate()->format('d/m/Y'),
                'price' => $variant->getPrice()
            ];
        } else {
            $dataToView = [
                'user' => $user->getUserProfile(),
                'number' => $user->getNumber(),
                'prestation' => $prestation->getLabel(),
                'duration' => $prestation->getDuration(),
                'hour' => $hour->getHour()->format('H:i'),
                'date' => $date->getDate()->format('d/m/Y'),
                'price' => $prestation->getPrice()
            ];
        }
        return $this->render('home/recapAppointment.html.twig', [
            'reservation' => $dataToView,
            'google_maps_api_key' => $_ENV['GOOGLE_MAPS_API_KEY']
        ]);
    }

    #[Route('/confirmation', name: 'app_confirmation-reservation')]
    public function confirmationPage(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Rediriger si non authentifié
        }
        $reservationDetails = $request->getSession()->remove('reservation_details');

        $appointment = $entityManager->getRepository(Hours::class)->findOneBy([
            'user' => $user
        ]);

        if (!$appointment) {
            $this->addFlash('error', 'Aucun rendez-vous actif trouvé.');
            return $this->redirectToRoute('app_cal');
        }

        $prestation = $appointment->getPrestation();
        $variant = $appointment->getVariant();

        // Récupération de l'objet Date issu de l'entité Date
        $dateEntity = $appointment->getDate();
        if (!$dateEntity) {
            throw new \Exception("Aucune date associée au rendez-vous.");
        }
        // On suppose que la méthode getDate() de l'entité Date retourne un objet DateTime
        $dateObj = $dateEntity->getDate();

        // On définit le fuseau horaire local (Europe/Paris)
        $localTz = new \DateTimeZone('Europe/Paris');
        $dateObj->setTimezone($localTz);

        // Récupération de l'heure du rendez-vous (stockée dans la table hours)
        $hourObj = $appointment->getHour();
        if (!$hourObj) {
            throw new \Exception("Aucune heure associée au rendez-vous.");
        }
        // On recrée l'objet heure en forçant le fuseau Europe/Paris (pour éviter toute confusion)
        $hourLocal = new \DateTime($hourObj->format('H:i:s'), $localTz);

        // Combinaison de la date et de l'heure pour obtenir le DateTime de début en heure locale
        $startDate = new \DateTime(
            $dateObj->format('Y-m-d') . ' ' . $hourLocal->format('H:i:s'),
            $localTz
        );

        // Calcul de la durée à partir de la variante ou de la prestation (format TIME)
        if ($variant) {
            $durationStr = $variant->getDuration()->format('H:i:s');
        } elseif ($prestation) {
            $durationStr = $prestation->getDuration()->format('H:i:s');
        } else {
            $durationStr = '00:00:00';
        }
        list($dH, $dM, $dS) = explode(':', $durationStr);
        $durationInterval = new \DateInterval("PT{$dH}H{$dM}M{$dS}S");

        // Calcul de la date de fin en ajoutant la durée au début
        $endDate = (clone $startDate)->add($durationInterval);

        // Conversion en UTC pour le format ICS (Google Calendar attend des dates en UTC)
        $startDateUtc = (clone $startDate)->setTimezone(new \DateTimeZone('UTC'));
        $endDateUtc   = (clone $endDate)->setTimezone(new \DateTimeZone('UTC'));

        // Format ICS : YYYYMMDDTHHMMSSZ
        $eventDtstart = $startDateUtc->format('Ymd\THis\Z');
        $eventDtend   = $endDateUtc->format('Ymd\THis\Z');

        // Définition du titre, de la description et de la localisation pour l'événement
        $eventTitle = "Rendez-vous " . ($variant ? $variant->getLabel() : ($prestation ? $prestation->getLabel() : ''));
        $eventDescription = "Rendez-vous Beauty by M.";


        // Préparation du tableau de données à transmettre à la vue (on ne touche pas aux données existantes)
        $dataToView = [
            'user'         => $user->getUserProfile(),
            'number'       => $user->getNumber(),
            'prestation'   => $variant ? $variant->getLabel() : ($prestation ? $prestation->getLabel() : 'Prestation inconnue'),
            'duration'     => $variant ? $variant->getDuration()->format('H:i') : ($prestation ? $prestation->getDuration()->format('H:i') : '00:00'),
            'hour'         => $appointment->getHour() ? $appointment->getHour()->format('H:i') : 'Non spécifiée',
            'date'         => $dateObj ? $dateObj->format('d/m/Y') : 'Date inconnue',
            'price'        => $variant ? $variant->getPrice() : ($prestation ? $prestation->getPrice() : 0),
            // Nouvelles clés pour la création dynamique du lien Google Calendar (et ICS)
            'eventTitle'       => $eventTitle,
            'eventDtstart'     => $eventDtstart,
            'eventDtend'       => $eventDtend,
            'eventDescription' => $eventDescription,
            'eventLocation' => 'Beauty By M 81 Rue Albert Cuenin, Dunkerque, France',
        ];

        return $this->render('home/confirmationAppointment.html.twig', [
            'reservation' => $dataToView,
            'google_maps_api_key' => $_ENV['GOOGLE_MAPS_API_KEY']
        ]);
    }



    #[Route('/cancel-appointment', name: 'app_cancel-appointment')]
    public function cancelAppointment(
        Security $security,
        EntityManagerInterface $entityManager,
        HoursRepository $hoursRepository,
        UserRepository $userRepository
    ): Response {
        $user = $security->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Rechercher la réservation de l'utilisateur connecté
        $hourEntity = $hoursRepository->findOneBy(['user' => $user]);

        // Vérifier si une réservation a été trouvée
        if (!$hourEntity) {
            $this->addFlash('error', 'Aucune réservation active n’a été trouvée.');
            return $this->redirectToRoute('app_home');
        }

        // Vérifier si l'utilisateur est valide
        if (!$user instanceof User) {
            throw new \Exception("L'utilisateur connecté n'est pas valide.");
        }

        $currentUser = $userRepository->find($user->getId());
        if (!$currentUser) {
            throw new \Exception("Utilisateur non trouvé dans la base de données.");
        }

        $appointmentDate = $hourEntity->getDate()->getDate();
        $appointmentTime = $hourEntity->getHour();
        $appointmentDateTime = new \DateTime(
            $appointmentDate->format('Y-m-d') . ' ' . $appointmentTime->format('H:i:s')
        );
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));

        $diffInHours = ($appointmentDateTime->getTimestamp() - $now->getTimestamp()) / 3600;

        if ($diffInHours < 48) {
            $this->addFlash('error', 'Il n’est plus possible d’annuler votre réservation à moins de 48 heures. Veuillez nous contacter si besoin.');
            return $this->redirectToRoute('app_cal');
        }

        // Créer une copie du rendez-vous dans OldAppointment
        $oldAppointment = new OldAppointment();
        $oldAppointment->setUser($currentUser);
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
        $hourEntity->setCancelToken(null);

        $entityManager->persist($oldAppointment);
        $entityManager->persist($hourEntity);
        $entityManager->flush();

        // Message de confirmation
        $this->addFlash('success', 'Votre réservation a bien été annulée.');

        return $this->redirectToRoute('app_cal');
    }

    #[Route('/profil', name: 'app_profile')]
    public function userProfil(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $this->getUser(); // Entité User

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $userProfile = $user->getUserProfile(); // Entité UserProfile associée

        // Créer le formulaire
        $form = $this->createForm(UserProfileType::class, $userProfile);
        $form->handleRequest($request);

        // Vérifier la soumission et la validation
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($userProfile);
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour.');

            return $this->redirectToRoute('app_profile');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $entityManager->refresh($userProfile);

        }

        return $this->render('home/userProfil.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/cgu', name: 'app_cgu')]
    public function cgu(): Response {
        return $this->render('home/CGU.html.twig');
    }

    #[Route('/mentions', name: 'app_mentions')]
    public function mentions(): Response {
        return $this->render('home/mentions.html.twig');
    }
}
