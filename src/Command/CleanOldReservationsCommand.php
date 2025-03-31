<?php

namespace App\Command;

use App\Entity\Date;
use App\Entity\OldAppointment;
use App\Repository\HoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clean-old-reservations',
    description: 'Archive les réservations passées et nettoie les entités Hours.'
)]
class CleanOldReservationsCommand extends Command
{
    private HoursRepository $hoursRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(HoursRepository $hoursRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->hoursRepository = $hoursRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        // Si besoin, vous pouvez ajouter ici des options/arguments supplémentaires
        // $this->addArgument('...')->addOption('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();

        $io->section('Archivage des réservations passées...');

        $pastReservations = $this->hoursRepository->findPastReservations($now);

        if (count($pastReservations) > 0) {
            foreach ($pastReservations as $reservation) {
                // Transférer les informations dans OldAppointment
                $oldAppointment = new OldAppointment();
                $oldAppointment->setHour($reservation->getHour());
                $oldAppointment->setDate($reservation->getDate()->getDate());
                $oldAppointment->setPrestation($reservation->getPrestation()?->getLabel());
                $oldAppointment->setVariant($reservation->getVariant()?->getLabel());
                $oldAppointment->setStatus('Terminé');
                $oldAppointment->setUser($reservation->getUser());

                $this->entityManager->persist($oldAppointment);

                // Vider les informations de l'entité Hours
                $reservation->setUser(null);
                $reservation->setPrestation(null);
                $reservation->setVariant(null);
                $this->entityManager->persist($reservation);

                $io->info("Réservation ID {$reservation->getId()} transférée et nettoyée.");
            }

            $this->entityManager->flush();
            $io->success(\count($pastReservations) . ' réservations passées ont été archivées et nettoyées.');
        } else {
            $io->info('Aucune réservation passée à archiver.');
        }

        // --- Suppression des entités Date (et leurs Hours associées) pour les jours antérieurs à aujourd'hui ---
        // Supposons que votre entité représentant un jour s'appelle "DateEntity"
        // et qu'elle possède une propriété "date" (de type \DateTime) et une relation "hours" vers l'entité Hours.

        // Récupérer le repository de l'entité DateEntity (n'oubliez pas d'importer la classe correspondante)
        $dateRepository = $this->entityManager->getRepository(Date::class);

        // Utiliser le QueryBuilder pour trouver les dates antérieures à aujourd'hui
        $oldDates = $dateRepository->createQueryBuilder('d')
            ->where('d.date < :today')
            ->setParameter('today', $now->setTime(0, 0, 0))
            ->getQuery()
            ->getResult();

        if (count($oldDates) > 0) {
            foreach ($oldDates as $oldDate) {
                // Affichage d'un message d'information
                $io->info("Suppression de la date " . $oldDate->getDate()->format('Y-m-d') . " et de ses heures associées.");

                // Si la suppression en cascade n'est pas configurée, supprimer manuellement les Hours associés
                foreach ($oldDate->getHours() as $hour) {
                    $this->entityManager->remove($hour);
                }
                // Supprimer l'entité DateEntity elle-même
                $this->entityManager->remove($oldDate);
            }
            $this->entityManager->flush();
            $io->success(count($oldDates) . ' jours et leurs heures associées ont été supprimés.');
        } else {
            $io->info("Aucune date antérieure à aujourd'hui n'a été trouvée pour suppression.");
        }

        return Command::SUCCESS;
    }
}
