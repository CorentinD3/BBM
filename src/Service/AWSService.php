<?php

namespace App\Service;

use App\Entity\SmsLog;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class AWSService
{
    private $snsClient;
    private $fromNumber;
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;


    public function __construct(string $awsKey, string $awsSecret, string $region, string $fromNumber, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->snsClient = new SnsClient([
            'version' => 'latest',
            'region'  => $region,
            'credentials' => [
                'key'    => $awsKey,
                'secret' => $awsSecret,
            ],
            'http' => [
                'verify' => false, // Désactive la vérification SSL
            ],
            ]);
        $this->logger = $logger;

        $this->fromNumber = $fromNumber;
        $this->entityManager = $entityManager;
    }

    public function sendSms(string $to, string $message, string $ipAddress): void
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

            // Enregistrer l'envoi dans la base de données
            $smsLog = new SmsLog();
            $smsLog->setIpAddress($ipAddress);
            $smsLog->setSentAt(new \DateTime());
            $smsLog->setNumber($to);
            $this->entityManager->persist($smsLog);
            $this->entityManager->flush();

            $this->logger->info('SMS sent', ['result' => $result]);
        } catch (AwsException $e) {
            $this->logger->error('Error sending SMS: ' . $e->getMessage());
            throw new \RuntimeException('Error sending SMS. Please try again.');
        }
    }
}