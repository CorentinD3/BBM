<?php

namespace App\Controller;

use App\Entity\Date;
use App\Entity\Hours;
use App\Entity\Prestations;
use App\Entity\Variant;
use App\Repository\DateRepository;
use App\Repository\HoursTemplateRepository;
use App\Repository\PrestationsRepository;
use App\Service\EmailSenderService;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CalendarController extends AbstractController
{

    #[Route('/api/hours-templates', methods: "GET")]
    public function getHoursTemplates(HoursTemplateRepository $hoursTemplateRepository, LoggerInterface $logger): JsonResponse
    {
        try {
            // Récupère tous les templates d'heures
            $templates = $hoursTemplateRepository->findAll();

            if (!$templates) {
                $logger->warning('No hour templates found in the database.');
                return new JsonResponse(['status' => 'error', 'message' => 'Aucune template trouvée.'], Response::HTTP_NOT_FOUND);
            }

            // Transforme les templates en un tableau simple pour le JSON
            $data = [];
            foreach ($templates as $template) {
                $data[] = [
                    'hour' => $template->getHour()->format('H:i'),
                ];
            }

            // Retourne une réponse JSON avec les templates
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            // Log message d'erreur
            $logger->error('Error fetching hour templates: ' . $e->getMessage());
            // Retourne une réponse JSON avec un statut d'erreur
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur lors de la récupération de données.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/date-hours', methods: "POST")]
    public function saveDateHours(Request $request, DateRepository $dateRepository, EntityManagerInterface $entityManager, LoggerInterface $logger, ValidatorInterface $validator): JsonResponse
    {
        // Décodage du corps de la requête JSON en un tableau associatif PHP
        $data = json_decode($request->getContent(), true);

        // Validation des données JSON reçues
        $constraint = new Assert\Collection([
            'date' => [new Assert\NotBlank(), new Assert\Date()],
            'hours' => [
                new Assert\NotBlank(),
                new Assert\All([
                    new Assert\Regex([
                        'pattern' => '/^(2[0-3]|[01]?[0-9]):([0-5]?[0-9])$/',
                        'message' => 'Chaque heure doit être au format HH:mm.',
                    ])
                ]),
            ],
        ]);

        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $logger->error('Validation échouée', ['errors' => (string) $violations]);
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur de validation des données fournies.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Tentative de création d'un objet DateTime à partir de la chaîne de caractères 'date'
            $dateObj = new \DateTime($data['date']);
        } catch (\Exception $e) {
            // Enregistrement de l'erreur et retour d'une réponse JSON
            $logger->error('Format de date invalide', ['date' => $data['date']]);
            return new JsonResponse(['status' => 'error', 'message' => 'Format de date invalide.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si la date existe déjà dans la base de données
        $dateEntity = $dateRepository->findOneBy(['date' => $dateObj]);
        if (!$dateEntity) {
            // Création d'une nouvelle entité Date si la date n'existe pas
            $dateEntity = new Date();
            $dateEntity->setDate($dateObj);
            $entityManager->persist($dateEntity);
        }

        // Parcours de chaque heure fournie dans les données de la requête
        foreach ($data['hours'] as $hour) {
            try {
                // Création d'un objet DateTime pour chaque heure
                $hourObj = new \DateTime($hour);
            } catch (\Exception $e) {
                // Log et ignoration de l'heure invalide
                $logger->error('Format d\'heure invalide', ['hour' => $hour]);
                continue;
            }

            // Vérifie si l'heure existe déjà pour cette date
            $existingHour = $dateEntity->getHours()->filter(function (Hours $existingHour) use ($hourObj) {
                return $existingHour->getHour()->format('H:i') == $hourObj->format('H:i');
            })->first();

            // Ajout de l'heure si elle n'existe pas déjà
            if (!$existingHour) {
                $hoursEntity = new Hours();
                $hoursEntity->setHour($hourObj);
                $hoursEntity->setDate($dateEntity);
                $entityManager->persist($hoursEntity);
            }
        }

        try {
            // Sauvegarde des modifications dans la base de données
            $entityManager->flush();
            $logger->info('Données enregistrées avec succès');
            return new JsonResponse(['status' => 'success', 'message' => 'Données enregistrées avec succès.'], Response::HTTP_OK);

        } catch (\Exception $e) {
            // Log de l'erreur et retour d'une réponse d'erreur
            $logger->error('Erreur lors de l\'enregistrement des données', ['exception' => $e->getMessage()]);
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur lors de l\'enregistrement des données.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    #[Route('/api/date-hours', methods: "DELETE")]
    public function deleteDateHours(Request $request, DateRepository $dateRepository, EntityManagerInterface $entityManager, LoggerInterface $logger, ValidatorInterface $validator): JsonResponse
    {
        // Décodage du corps de la requête JSON en un tableau associatif PHP
        $data = json_decode($request->getContent(), true);

        // Validation des données JSON reçues
        $constraint = new Assert\Collection([
            'date' => [new Assert\NotBlank(), new Assert\Date()],
            'hours' => [
                new Assert\NotBlank(),
                new Assert\All([
                    new Assert\Regex([
                        'pattern' => '/^(2[0-3]|[01]?[0-9]):([0-5]?[0-9])$/',
                        'message' => 'Chaque heure doit être au format HH:mm.',
                    ])
                ]),
            ],
        ]);

        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $logger->error('Échec de validation des données', ['errors' => (string) $violations]);
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur de validation des données fournies.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Création de l'objet DateTime à partir de la chaîne 'date'
            $dateObj = new \DateTime($data['date']);
        } catch (\Exception $e) {
            $logger->error('Format de date invalide', ['date' => $data['date']]);
            return new JsonResponse(['status' => 'error', 'message' => 'Format de date invalide.'], Response::HTTP_BAD_REQUEST);
        }

        // Recherche de l'entité Date dans la base de données
        $dateEntity = $dateRepository->findOneBy(['date' => $dateObj]);

        if (!$dateEntity) {
            $logger->error('Entité Date non trouvée pour suppression', ['date' => $data['date']]);
            return new JsonResponse(['status' => 'error', 'message' => 'Date introuvable.'], Response::HTTP_NOT_FOUND);
        }

        foreach ($data['hours'] as $hour) {
            try {
                $hourObj = new \DateTime($hour);
            } catch (\Exception $e) {
                $logger->error('Format d\'heure invalide pour suppression', ['hour' => $hour]);
                continue; // Ignore l'heure invalide et passe à la suivante
            }

            $hourEntity = $entityManager->getRepository(Hours::class)->findOneBy([
                'date' => $dateEntity,
                'hour' => $hourObj
            ]);

            if ($hourEntity) {
                $entityManager->remove($hourEntity);
                $logger->info('Entité Hours supprimée', ['hour' => $hour]);
            } else {
                $logger->info('Entité Hours introuvable pour suppression', ['hour' => $hour]);
            }
        }

        try {
            $entityManager->flush();
            $logger->info('Heures supprimées avec succès');
            return new JsonResponse(['status' => 'success', 'message' => 'Données supprimées avec succès.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $logger->error('Erreur lors de la suppression', ['exception' => $e->getMessage()]);
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur lors de la suppression des données.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/dates', methods: "GET")]
    public function getSavedDates(DateRepository $dateRepository, Security $security): JsonResponse
    {
        $dates = $dateRepository->findAll();
        $currentUser = $security->getUser();
        $currentUserId = $currentUser ? $currentUser->getId() : null;

        $userAppointment = null;
        $response = [];

        foreach ($dates as $date) {
            $hours = [];
            foreach ($date->getHours() as $hour) {
                $userEntity = $hour->getUser();
                $userData = null;
                if ($userEntity) {
                    $userProfile = $userEntity->getUserProfile();
                    $userData = [
                        'id' => $userEntity->getId(),
                        'number' => $userEntity->getNumber(),
                        // On inclut les informations du profil si elles existent
                        'firstName' => $userProfile ? $userProfile->getFirstName() : null,
                        'lastName'  => $userProfile ? $userProfile->getLastName() : null,
                    ];
                }

                $hours[] = [
                    'id' => $hour->getId(),
                    'hour' => $hour->getHour()->format('H:i'),
                    'user' => $userData,  // On retourne un tableau avec les infos utilisateur ou null
                    'prestation' => $hour->getPrestation() ? [
                        'id' => $hour->getPrestation()->getId(),
                        'label' => $hour->getPrestation()->getLabel(),
                        'duration' => $hour->getPrestation()->getDuration()->format('H:i:s'),
                        'price' => $hour->getPrestation()->getPrice()
                    ] : null,
                    'variant' => $hour->getVariant() ? [
                        'id' => $hour->getVariant()->getId(),
                        'label' => $hour->getVariant()->getLabel(),
                        'duration' => $hour->getVariant()->getDuration()->format('H:i:s'),
                        'price' => $hour->getVariant()->getPrice()
                    ] : null
                ];

                // Si l'heure est réservée par l'utilisateur actuel, on sauvegarde le rendez-vous
                if ($userEntity && $userEntity->getId() === $currentUserId) {
                    $userAppointment = [
                        'date' => $date->getDate()->format('Y-m-d'),
                        'hour' => $hour->getHour()->format('H:i'),
                        'id' => $hour->getId(),
                        'prestation' => $hour->getPrestation() ? [
                            'id' => $hour->getPrestation()->getId(),
                            'label' => $hour->getPrestation()->getLabel(),
                            'duration' => $hour->getPrestation()->getDuration()->format('H:i:s'),
                            'price' => $hour->getPrestation()->getPrice()
                        ] : null,
                        'variant' => $hour->getVariant() ? [
                            'id' => $hour->getVariant()->getId(),
                            'label' => $hour->getVariant()->getLabel(),
                            'duration' => $hour->getVariant()->getDuration()->format('H:i:s'),
                            'price' => $hour->getVariant()->getPrice()
                        ] : null
                    ];
                }
            }
            $response[] = [
                'date' => $date->getDate()->format('Y-m-d'),
                'hours' => $hours,
            ];
        }

        return new JsonResponse([
            'dates' => $response,
            'userAppointment' => $userAppointment,
            'currentUserId' => $currentUserId,

        ]);
    }

    #[Route('/api/prestations', methods: "GET")]
    public function getPrestations(PrestationsRepository $prestationsRepository, LoggerInterface $logger): JsonResponse
    {
        try {
            $prestations = $prestationsRepository->findAll();

            if (!$prestations) {
                $logger->warning('Aucune prestation trouvée dans la base de données.');
                return new JsonResponse(['status' => 'error', 'message' => 'Aucune prestation trouvée. Veuillez ajouter des prestations.'], Response::HTTP_NOT_FOUND);
            }

            // Ajuster pour que chaque prestation renvoyée ait le bon format
            $response = [];
            foreach ($prestations as $prestation) {
                $response[] = [
                    'id' => $prestation->getId(),
                    'label' => $prestation->getLabel(),
                    'duration' => $prestation->getDuration()->format('H:i:s'),
                    'price' => $prestation->getPrice(),
                    'variants' => array_map(function ($variant) {
                        return [
                            'id' => $variant->getId(),
                            'label' => $variant->getLabel(),
                            'duration' => $variant->getDuration()->format('H:i:s'),
                            'price' => $variant->getPrice(),
                        ];
                    }, $prestation->getVariants()->toArray()), // Inclure les variantes
                ];
            }

            return new JsonResponse(['prestations' => $response], Response::HTTP_OK);
        } catch (\Exception $e) {
            $logger->error('Erreur lors de la récupération des prestations : ' . $e->getMessage());
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur lors de la récupération des prestations. Veuillez réessayer plus tard.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/confirm-appointment', name: "api_confirm-appointment", methods: "POST")]
    public function confirmAppointment(Request $request, EntityManagerInterface $entityManager, Security $security, LoggerInterface $logger): JsonResponse {
        // Décoder les données JSON envoyées par la requête
        $data = json_decode($request->getContent(), true);
        $hourId = $data['hourId'] ?? null;
        $prestationId = $data['prestationId'] ?? null;
        $variantId = $data['variantId'] ?? null;
        $user = $security->getUser();

        // Vérification des données d'entrée
        if (!$hourId || !$prestationId || !$user) {
            $logger->error('Données manquantes ou utilisateur non authentifié lors de la confirmation du rendez-vous.');
            return new JsonResponse(['status' => 'error', 'message' =>  'Données manquantes ou utilisateur non authentifié.'], Response::HTTP_BAD_REQUEST);
        }



        $variant = $variantId ? $entityManager->getRepository(Variant::class)->find($variantId) : null;

        // Récupérer le créneau horaire (Hour) et la prestation à partir des IDs
        $hour = $entityManager->getRepository(Hours::class)->find($hourId);
        $prestation = $entityManager->getRepository(Prestations::class)->find($prestationId);

        if (!$hour || !$prestation) {
            $logger->warning('Heure ou prestation non trouvée pour l\'ID spécifié.', ['hourId' => $hourId, 'prestationId' => $prestationId]);
            return new JsonResponse(['status' => 'error', 'message' => 'Heure ou prestation non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        // Convertir l'heure de début en minutes depuis minuit
        $startTime = strtotime($hour->getHour()->format('H:i'));

// Calculer la durée à partir de la variante ou de la prestation
        $durationInMinutes = $variant ? $variant->getDurationInMinutes() : $prestation->getDurationInMinutes();

// Calculer l'heure de fin
        $endTime = $startTime + ($durationInMinutes * 60);

// Récupérer toutes les réservations pour cette date
        $reservations = $entityManager->getRepository(Hours::class)->findBy(['date' => $hour->getDate()]);

// Vérifier les conflits avec les réservations existantes
        foreach ($reservations as $existingReservation) {
            if ($existingReservation->getUser() !== null) {
                $existingStartTime = strtotime($existingReservation->getHour()->format('H:i'));

                // Calculer la durée de la prestation existante
                $existingDurationInMinutes = $existingReservation->getVariant()
                    ? $existingReservation->getVariant()->getDurationInMinutes()
                    : $existingReservation->getPrestation()->getDurationInMinutes();

                $existingEndTime = $existingStartTime + ($existingDurationInMinutes * 60);

                // Vérifie le conflit de chevauchement
                if (($startTime < $existingEndTime) && ($endTime > $existingStartTime)) {
                    $logger->warning('Conflit avec une réservation existante pour le créneau sélectionné.', ['hourId' => $hourId]);
                    return new JsonResponse(['status' => 'error', 'message' => 'Conflit avec une réservation existante.'], Response::HTTP_CONFLICT);
                }
            }
        }

        // Stockage des informations dans la session
        try {
            $request->getSession()->set('reservation_details', [
                'hour' => $hour,
                'prestation' => $prestation,
                'variant' => $variant,
            ]);
            return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $logger->error('Erreur lors de l\'enregistrement des détails de réservation dans la session.', ['exception' => $e->getMessage()]);
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur lors de la confirmation du rendez-vous.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/finalize-appointment', name: 'api_finalize-appointment', methods: "POST")]
    public function finalizeAppointment(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        LoggerInterface $logger,
        EmailSenderService $emailSenderService
    ): JsonResponse {
        $user = $security->getUser();

        if (!$user instanceof \App\Entity\User) {
            $logger->error('Tentative de finalisation sans utilisateur authentifié ou type utilisateur incorrect.');
            return new JsonResponse(['status' => 'error', 'message' => 'Utilisateur non authentifié ou type incorrect.'], Response::HTTP_UNAUTHORIZED);
        }

        $session = $request->getSession();

        if (!$session->has('reservation_details')) {
            $logger->warning('Aucune réservation en attente de confirmation trouvée dans la session.');
            return new JsonResponse(['status' => 'error', 'message' => 'Aucune réservation en attente de confirmation.'], Response::HTTP_BAD_REQUEST);
        }

        $reservationDetails = $session->get('reservation_details');
        $hourId = $reservationDetails['hour'];
        $prestationId = $reservationDetails['prestation'];
        $variantId = $reservationDetails['variant'] ?? null;

        $hour = $entityManager->getRepository(Hours::class)->find($hourId);
        if (!$hour || $hour->getUser() !== null) {
            $logger->info('Créneau déjà réservé ou indisponible.', ['hourId' => $hourId]);
            return new JsonResponse(['status' => 'error', 'message' => 'Le créneau est déjà réservé ou indisponible.'], Response::HTTP_CONFLICT);
        }

        $prestation = $entityManager->getRepository(Prestations::class)->find($prestationId);
        if (!$prestation) {
            $logger->error('Prestation introuvable.', ['prestationId' => $prestationId]);
            return new JsonResponse(['status' => 'error', 'message' => 'Prestation introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $variant = null;
        if ($variantId) {
            $variant = $entityManager->getRepository(Variant::class)->find($variantId);
            if (!$variant) {
                $logger->error('Variante introuvable.', ['variantId' => $variantId]);
                return new JsonResponse(['status' => 'error', 'message' => 'Variante introuvable.'], Response::HTTP_NOT_FOUND);
            }
        }

        try {
            $hour->setUser($user);
            $hour->setCancelToken(Uuid::v4());

            if ($variant) {
                $hour->setPrestation($variant->getPrestation());
                $hour->setVariant($variant);
            } else {
                $hour->setPrestation($prestation);
            }

            $entityManager->persist($hour);
            $entityManager->flush();

            $session->remove('reservation_details');

            $dateObj = $hour->getDate()->getDate();
            $hourObj = $hour->getHour();

            $appointmentDateTime = new \DateTime($dateObj->format('Y-m-d').' '.$hourObj->format('H:i:s'));
            $cancelLimitDate = (clone $appointmentDateTime)->modify('-48 hours');

            setlocale(LC_TIME, 'fr_FR.UTF-8');

            if ($variant) {
                $eventTitle = "Rendez-vous " . $variant->getLabel();
            } elseif ($prestation) {
                $eventTitle = "Rendez-vous " . $prestation->getLabel();
            } else {
                $eventTitle = "Rendez-vous";
            }

            $startDateUtc = (clone $appointmentDateTime)->setTimezone(new \DateTimeZone('UTC'));
            $eventDtstart = $startDateUtc->format('Ymd\THis\Z');

            $duration = $variant ? $variant->getDuration() : ($prestation ? $prestation->getDuration() : null);
            $durationInterval = $duration instanceof \DateInterval ? $duration : new \DateInterval('PT0H0M0S');

            $endDateUtc  = (clone $startDateUtc)->add($durationInterval);
            $eventDtend  = $endDateUtc->format('Ymd\THis\Z');

            $cancelToken = $hour->getCancelToken();

            $dataToView = [
                'user'             => $user->getUserProfile(),
                'number'           => $user->getNumber(),
                'prestation'       => $variant ? ($prestation->getLabel() . ' / ' . $variant->getLabel()) : ($prestation ? $prestation->getLabel() : 'Prestation inconnue'),
                'duration'         => $variant ? $variant->getDuration()->format('H:i') : ($prestation ? $prestation->getDuration()->format('H:i') : '00:00'),
                'hour'             => $hourObj->format('H:i'),
                'date'             => $dateObj,
                'price'            => $variant ? $variant->getPrice() : ($prestation ? $prestation->getPrice() : 0),
                'cancelLimit'      => $cancelLimitDate,
                'eventLocation'    => 'Beauty By M, 81 Rue Albert Cuenin, Dunkerque, France',
                'cancelToken'      => $cancelToken,
                'eventDtstart'     => $eventDtstart,
                'eventDtend'       => $eventDtend,
                'eventTitle'       => $eventTitle,
                'eventDescription' => "Rendez-vous Beauty By M"
            ];
            // Envoi de l'e-mail au client
            $emailSenderService->sendConfirmationEmail($dataToView, $user->getUserProfile()->getEmail());
            $logger->info('E-mail de confirmation envoyé au client.', [
                'email' => $user->getUserProfile()->getEmail(),
                'hourId' => $hour->getId()
            ]);
            // Envoi de l'e-mail à l'administrateur
            $adminEmail = 'mathlaly@live.fr'; // À remplacer par une vraie adresse ou un paramètre configurable
            $emailSenderService->sendConfirmationEmailAdmin($dataToView, $adminEmail);
            $logger->info('E-mail de notification envoyé à l\'administrateur.', [
                'email' => $adminEmail,
                'hourId' => $hour->getId()
            ]);

            return new JsonResponse(['status' => 'success', 'message' => 'Réservation confirmée et e-mail envoyé.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $logger->error('Erreur lors de la confirmation de la réservation.', ['exception' => $e]);
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Erreur lors de la confirmation.',
                'exception' => [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




    #[Route('/download-appointment', name: 'download_appointment')]
    public function downloadAppointment(EntityManagerInterface $entityManager, Security $security): Response
    {
        // Vérification de l'utilisateur connecté
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupération du rendez-vous actif dans la table Hours (selon votre logique)
        $appointment = $entityManager->getRepository(Hours::class)->findOneBy([
            'user' => $user,
        ]);

        if (!$appointment) {
            $this->addFlash('error', 'Aucun rendez-vous actif trouvé.');
            return $this->redirectToRoute('app_cal');
        }

        // On définit le fuseau horaire local souhaité : Europe/Paris
        $localTz = new DateTimeZone('Europe/Paris');

        /*
         * Récupération et préparation de la date :
         * - $appointment->getDate() retourne une instance de l’entité Date.
         * - On récupère l'objet DateTime via ->getDate() de cette entité.
         */
        $dateEntity = $appointment->getDate();
        if (!$dateEntity) {
            throw new \Exception('Aucune date associée au rendez-vous.');
        }
        $date = $dateEntity->getDate(); // objet DateTime (date seule)
        // On s'assure que la date est en Europe/Paris
        $date->setTimezone($localTz);

        /*
         * Récupération et préparation de l'heure :
         * - $appointment->getHour() retourne un objet DateTime (la partie date n'est pas utile).
         * - On extrait l'heure, les minutes et les secondes, en forçant le fuseau Europe/Paris.
         */
        $time = $appointment->getHour();
        if (!$time) {
            throw new \Exception('Aucune heure associée au rendez-vous.');
        }
        // Recréer l'heure à partir de sa chaîne, en précisant le fuseau Europe/Paris
        $timeLocal = new DateTime($time->format('H:i:s'), $localTz);

        /*
         * Combinaison de la date et de l'heure pour obtenir le début de l'événement en heure locale.
         * On utilise le format 'Y-m-d' de la date et on y ajoute l'heure formatée.
         */
        $startDate = new DateTime(
            $date->format('Y-m-d') . ' ' . $timeLocal->format('H:i:s'),
            $localTz
        );

        /*
         * Calcul de la date de fin à partir de la durée.
         * On vérifie si une variante existe, sinon on prend la durée de la prestation.
         * Les durées sont stockées au format TIME (exemple : "01:30:00").
         */
        if ($appointment->getVariant()) {
            $durationStr = $appointment->getVariant()->getDuration()->format('H:i:s');
        } elseif ($appointment->getPrestation()) {
            $durationStr = $appointment->getPrestation()->getDuration()->format('H:i:s');
        } else {
            $durationStr = '00:00:00';
        }
        list($dHours, $dMinutes, $dSeconds) = explode(':', $durationStr);
        $durationInterval = new DateInterval("PT{$dHours}H{$dMinutes}M{$dSeconds}S");

        $endDate = (clone $startDate)->add($durationInterval);

        /*
         * Conversion en UTC pour le fichier ICS.
         * Par exemple, si l'événement commence à 09:15 en Europe/Paris (en hiver, UTC+1),
         * la conversion donnera 08:15Z.
         */
        $startDateUtc = (clone $startDate)->setTimezone(new DateTimeZone('UTC'));
        $endDateUtc   = (clone $endDate)->setTimezone(new DateTimeZone('UTC'));

        // Formatage des dates au format ICS : YYYYMMDDTHHMMSSZ
        $dtstamp = (new DateTime('now', new DateTimeZone('UTC')))->format('Ymd\THis\Z');
        $dtstart = $startDateUtc->format('Ymd\THis\Z');
        $dtend   = $endDateUtc->format('Ymd\THis\Z');

        // Préparation des autres informations de l'événement
        $title = "Rendez-vous ";
        if ($appointment->getVariant()) {
            $title .= $appointment->getVariant()->getLabel();
        } elseif ($appointment->getPrestation()) {
            $title .= $appointment->getPrestation()->getLabel();
        }
        $description = "Rendez-vous Beauty by M.";
        $location = "À définir";

        // Construction d'un UID unique (ici, à partir de l'ID du rendez-vous)
        $uid = $appointment->getId() . '@beauty-bym.fr';

        // Construction du contenu ICS
        $icsLines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Beauty by M//Appointment//FR',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . $dtstamp,
            'DTSTART:' . $dtstart,
            'DTEND:' . $dtend,
            'SUMMARY:' . $title,
            'DESCRIPTION:' . $description,
            'LOCATION:' . 'Beauty By M 81 Rue Albert Cuenin, Dunkerque, France',
            'END:VEVENT',
            'END:VCALENDAR'
        ];
        $icsContent = implode("\r\n", $icsLines);

        // Préparation de la réponse pour téléchargement du fichier ICS
        $response = new Response($icsContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'rendez-vous.ics'
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');

        return $response;
    }
}
