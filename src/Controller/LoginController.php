<?php

namespace App\Controller;

use App\Entity\SmsLog;
use App\Entity\User;
use App\Form\PhoneNumberType;
use App\Repository\SmsLogRepository;
use App\Repository\UserRepository;
use App\Service\OtpService;
use DateTime;
use DateTimeInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginController extends AbstractController

{
    private OtpService $otpService;
    private SmsLogRepository $smsLogRepository;
    private Security $security;
    private UserRepository $userRepository;

    public function __construct(OtpService $otpService, SmsLogRepository $logRepository, Security $security, UserRepository $userRepository)
    {
        $this->otpService = $otpService;
        $this->smsLogRepository = $logRepository;
        $this->security = $security;
        $this->userRepository = $userRepository;
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request, SessionInterface $session, Security $security): Response
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            return $this->redirectToRoute("app_home");
        }
        $form = $this->createForm(PhoneNumberType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $number = '+33' . implode('', array_values($data));

            $ipAddress = $request->getClientIp();


            if (empty($number) || !preg_match('/^\+33[0-9]{9}$/', $number)) {
                $this->addFlash('error', 'Le numéro de téléphone n\'est pas valide. Veuillez entrer un numéro français au format +33 suivi de 9 chiffres.');
                return $this->redirectToRoute('app_login');
            }

            //Verifier que l'utilisateur n'est pas banni
            $existingUser = $this->userRepository->findOneBy(['number' => $number]);
            if ($existingUser && null !== $existingUser->getBanList()) {
                $this->addFlash('error', 'Votre compte est banni. Impossible de se connecter.');
                return $this->redirectToRoute('app_login');
            }

            // Vérifier la limite par numéro
            $smsSentTodayByNumber = $this->smsLogRepository->findSmsSentTodayByNumber($number);
            if (count($smsSentTodayByNumber) >= 5) {
                $this->addFlash('error', 'Vous avez atteint la limite d\'envoi de SMS pour ce numéro aujourd\'hui.');
                return $this->redirectToRoute("app_login");
            }

            // Vérifier la limite par IP
            $smsSentTodayByIp = $this->smsLogRepository->findSmsSentTodayByIp($ipAddress);
            if (count($smsSentTodayByIp) >= 5) {
                $this->addFlash('error', 'Vous avez atteint la limite d\'envoi de SMS depuis cette adresse IP aujourd\'hui.');
                return $this->redirectToRoute("app_login");
            }

            $redirect = $request->query->get('redirect', $this->generateUrl('app_home'));

            $currentDateTime = new DateTime();
            $otp = $this->otpService->generateOtp();
            $otpCreationTime = $currentDateTime;
            $expirationTime = $this->otpService->createExpirationTime($otpCreationTime);

            // Récupère les données de session
            $send = $session->get('send', 0);
            $storedOtpExpirationTime = $session->get('otp_expiration_time');
            $storedNumber = $session->get('number');
            $isOtpExpired = !$storedOtpExpirationTime || new DateTime($storedOtpExpirationTime) < $currentDateTime;

            // Vérifier le nombre de SMS envoyés aujourd'hui par cette IP
            $smsSentToday = $this->smsLogRepository->findSmsSentTodayByIp($ipAddress);
            if (count($smsSentToday) >= 5) {
                $this->addFlash('error', 'Vous avez atteint la limite d\'envoi de SMS pour aujourd\'hui.');
                return $this->redirectToRoute("app_login");
            }

            if ($send == 0 || $isOtpExpired || $number !== $storedNumber) {
                // Envoi de l'OTP
                $this->otpService->sendOtp($number, $otp, $ipAddress);

                // Enregistrer l'envoi dans la base
                $log = new SmsLog();
                $log->setIpAddress($ipAddress);
                $log->setNumber($number);
                $log->setSentAt(new \DateTime());
                $this->smsLogRepository->add($log, true);

                // Mise à jour des informations dans la session
                $session->set('otp', $otp);
                $session->set('otp_creation_time', $otpCreationTime->format(DateTimeInterface::ISO8601));
                $session->set('otp_expiration_time', $expirationTime->format(DateTimeInterface::ISO8601));
                $session->set('number', $number);
                $session->set('send', 1);
            }

            // Redirection vers la page de validation de l'OTP
            return $this->redirectToRoute('app_validate-otp', ['redirect' => $redirect]);
        }

        return $this->render('auth/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/validate-otp', name: 'app_validate-otp')]
    public function showOtpForm(Request $request, SessionInterface $session): Response
    {
        $number = $session->get('number');
        $redirect = $request->query->get('redirect', $this->generateUrl('app_home'));
        $session->set('redirect', $redirect); // Stocker la redirection dans la session
        return $this->render('auth/validate_otp.html.twig', [
            'number' => $number,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}