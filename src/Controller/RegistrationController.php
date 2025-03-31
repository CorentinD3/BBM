<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\RegistrationFormType;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class RegistrationController extends AbstractController
{

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // Manually authenticate the user
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            try {
                $this->container->get('security.token_storage')->setToken($token);
            } catch (NotFoundExceptionInterface $e) {
                throw new AuthenticationException('Token not found', 0, $e);
            }


            try {
                $this->container->get('session')->set('_security_main', serialize($token));
            } catch (NotFoundExceptionInterface $e) {
                throw new AuthenticationException('Session service not found', 0, $e);
            }


            if ($user->getUserProfile() === null) {
                // Rediriger vers la page pour remplir les informations supplémentaires
                return $this->redirectToRoute('app_register-complete');
            } else {
            return $this->redirectToRoute('app_home');
            }
        }


        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/register/complete', name: 'app_register-complete')]
    public function complete(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_home');
        }

        // Vérifie si l'utilisateur a déjà un profil
        if ($user->getUserProfile() !== null) {
            return $this->redirectToRoute('app_home');
        }

        // Activer l'autorisation temporaire dans la session pour éviter les redirections

        $userProfile = new UserProfile();
        $userProfile->setUser($user); // Associe l'utilisateur à UserProfile

        $form = $this->createForm(UserProfileType::class, $userProfile, [
            'csrf_protection' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($userProfile);
            $em->flush();
            $this->addFlash('success', 'Bienvenue' .' '. $userProfile->getFirstName());
            // Supprime l'autorisation temporaire après la complétion
            return $this->redirectToRoute('app_home');
        }

        return $this->render('auth/complete.html.twig', [
            'userProfileForm' => $form->createView(),
        ]);
    }
}
