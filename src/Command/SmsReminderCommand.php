<?php

namespace App\Command;

use App\Repository\HoursRepository;
use App\Service\ReminderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-sms-reminder',
    description: 'Envoie un SMS de rappel aux clients ayant un rendez-vous demain.'
)]
class SmsReminderCommand extends Command
{
    private HoursRepository $hoursRepository;
    private ReminderService $reminderService;
    private EntityManagerInterface $entityManager;

    public function __construct(HoursRepository $hoursRepository, ReminderService $reminderService, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->hoursRepository = $hoursRepository;
        $this->reminderService = $reminderService;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tomorrow = (new \DateTime())->modify('+1 day')->setTime(0, 0, 0);

        $io->section('Recherche des rendez-vous de demain...');

        $appointments = $this->hoursRepository->findAppointmentsForDate($tomorrow);

        if (empty($appointments)) {
            $io->info('Aucun rendez-vous prévu pour demain.');
            return Command::SUCCESS;
        }

        foreach ($appointments as $appointment) {
            $user = $appointment->getUser();

            if (!$user || !$user->getNumber()) {
                continue;
            }

            $phoneNumber = $user->getNumber();
            $name = $user->getUserProfile()->getFirstName() ?? 'Cher client';
            $date = $appointment->getDate()->getDate()->format('d/m/Y');
            $hour = $appointment->getHour()->format('H:i');
            $prestation = $appointment->getPrestation()?->getLabel() ?? 'Non précisée';
            $variant = $appointment->getVariant()?->getLabel();
            $prestationText = $variant ? "$prestation - $variant" : $prestation;
            $duration = $appointment->getPrestation()?->getDuration();
            $duration = $duration instanceof \DateTime ? $duration->format('H:i') : ($duration ?? 'Non précisée');
            $location = "81 Rue Albert Cuenin, Dunkerque, France";

            $message = "Bonjour $name,\n\nVous avez un rendez-vous programmé demain à $hour.\n\nPrestation : $prestationText\nDurée : $duration\nLieu : $location\n\nMerci de votre ponctualité. À bientôt !";


            try {
                $this->reminderService->sendReminder($phoneNumber, $message);
                $io->success("SMS de rappel envoyé à $phoneNumber pour le rendez-vous du $date à $hour.");
            } catch (\Exception $e) {
                $io->error("Erreur lors de l'envoi du rappel à $phoneNumber : " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
