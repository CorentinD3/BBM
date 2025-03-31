<?php

namespace App\Security;

use App\Entity\User;
use App\Service\OtpService;
use DateTime;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class OTPAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;
    private OtpService $otpService;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, RouterInterface $router, OtpService $otpService, UrlGeneratorInterface $urlGenerator)
    {
        $this->otpService = $otpService;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->urlGenerator = $urlGenerator;

    }

    public function supports(Request $request): ?bool
    {
        // authentication est supportée uniquement sur la route de validation de l'OTP
        return $request->attributes->get('_route') === 'app_validate-otp' && $request->isMethod('POST');
    }



    public function authenticate(Request $request): Passport
    {
        $session = $request->getSession();
        $number = trim($session->get('number'));
        $lockoutUntil = $session->get('lockout_until');
        $currentTime = new DateTime();
        $csrfToken = $request->request->get('_csrf_token', '');

        // Vérifiez si le verrouillage est actif ou expiré
        if ($lockoutUntil) {
            if ($lockoutUntil instanceof DateTime && $currentTime < $lockoutUntil) {
                throw new AuthenticationException('Vous êtes temporairement bloqué. Réessayez plus tard.');
            }

            // Si le verrouillage est expiré, réinitialisez-le
            if ($lockoutUntil instanceof DateTime && $currentTime >= $lockoutUntil) {
                $session->remove('lockout_until');
                $session->set('failed_attempts', 0);
            }
        }

        // Concaténation des champs OTP
        $submittedOtp = '';
        for ($i = 1; $i <= 6; $i++) {
            $submittedOtp .= $request->request->get('otp_' . $i, '');
        }


        $storedOtp = trim($session->get('otp'));
        $otpExpirationTimeStr = $session->get('otp_expiration_time');
        $otpExpirationTime = new DateTime($otpExpirationTimeStr);


        // Vérifie si l'OTP a expiré
        if ($this->otpService->isOtpExpired($otpExpirationTime)) {
            throw new AuthenticationException('Code OTP expiré.');
        }

        // Vérifie si l'OTP soumis est incorrect
        if ($submittedOtp != $storedOtp) {
            $failedAttempts = $session->get('failed_attempts', 0);
            $failedAttempts++;
            $session->set('failed_attempts', $failedAttempts);

            if ($failedAttempts >= 5) {
                $lockoutUntil = (new DateTime())->add(new \DateInterval('PT15M')); // 15 minutes
                $session->set('lockout_until', $lockoutUntil);
                $session->set('failed_attempts', 0); // Réinitialiser les tentatives après le verrouillage
                throw new AuthenticationException('Trop de tentatives échouées. Veuillez réessayer plus tard.');
            }

            throw new AuthenticationException('Code OTP incorrect.');
        }

        // Réinitialise les tentatives échouées après une connexion réussie
        $session->set('failed_attempts', 0);
        $session->set('lockout_until', null);
        $session->set('send', 0);

        $user = $this->userRepository->findOneBy(['number' => $number]);

        if (!$user) {
            $user = new User();
            $user->setNumber($number);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return new SelfValidatingPassport(
            new UserBadge($number, function ($userIdentifier) use ($user) {
                return $user;
            }),
            [
                new CsrfTokenBadge('otp_auth', $csrfToken),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Récupérer l'utilisateur à partir du TokenInterface
        $user = $token->getUser();
        $session = $request->getSession();
        $redirect = $session->get('redirect', $this->router->generate('app_home'));
        $session->remove('redirect'); // Supprimer la redirection après utilisation

        if ($user instanceof User) {
            // Si l'utilisateur n'a pas de UserProfile, rediriger vers la page d'enregistrement
            if ($user->getUserProfile() === null) {
                return new RedirectResponse($this->router->generate('app_register-complete'));
            }
            $request->getSession()->getFlashBag()->add('success', 'Bonjour' .' '. $user->getUserProfile()->getFirstName());
            // Sinon, rediriger vers la page d'accueil
            return new RedirectResponse($redirect);
        }

        // Rediriger vers la page d'accueil par défaut si l'utilisateur n'est pas valide
        return new RedirectResponse($this->router->generate('app_logout'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Ajoutez un message flash pour afficher l'erreur
        $request->getSession()->getFlashBag()->add('error', $exception->getMessage());
        return new RedirectResponse($this->router->generate('app_validate-otp'));
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $request->getSession()->getFlashBag()->add('error', 'Accès refusé : vous n\'avez pas le niveau d\'accréditation requis pour accéder à cette ressource.');
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }
}