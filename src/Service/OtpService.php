<?php

namespace App\Service;

use DateTime;
use DateInterval;

class OtpService
{
    private AWSService $smsService;

    public function __construct(AWSService $AWSService)
    {
        $this->smsService = $AWSService;
    }

    public function generateOtp(): string
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function sendOtp(string $number, string $otp, string $ipAddress): void
    {
        $message = "Votre code OTP est : $otp";
        $this->smsService->sendSms($number, $message, $ipAddress);
    }

    public function isOtpExpired(DateTime $expirationTime): bool
    {
        return new DateTime() > $expirationTime;
    }

    public function createExpirationTime(DateTime $creationTime): DateTime
    {
        $validityPeriod = new DateInterval('PT10M');  // 10 minutes
        return (clone $creationTime)->add($validityPeriod);
    }
}