<?php

namespace App\Service;

use App\Entity\SmsLog;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ReminderService
{
    private SnsClient $snsClient;
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private string $fromNumber;

    public function __construct(string $awsKey, string $awsSecret, string $region, string $fromNumber, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->snsClient = new SnsClient([
            'version' => 'latest',
            'region'  => $region,
            'credentials' => [
                'key'    => $awsKey,
                'secret' => $awsSecret,
            ],

        ]);
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->fromNumber = $fromNumber;
    }

    public function sendReminder(string $to, string $message): void
    {
        try {
            $result = $this->snsClient->publish([
                'Message' => $message,
                'PhoneNumber' => $to,
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SenderID' => [
                        'DataType' => 'String',
                        'StringValue' => $this->fromNumber
                    ],
                ],
            ]);
            $this->logger->info('Rappel SMS envoyé', ['result' => $result]);
        } catch (AwsException $e) {
            $this->logger->error('Erreur lors de l\'envoi du rappel SMS : ' . $e->getMessage());
            throw new \RuntimeException('Erreur lors de l\'envoi du SMS de rappel.');
        }
    }
}
