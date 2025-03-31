<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class ProfileCompletionListener
{
    private Security $security;
    private RouterInterface $router;

    public function __construct(Security $security, RouterInterface $router)
    {
        $this->security = $security;
        $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        // Vérifie que la requête est principale
        if (!$event->isMainRequest()) {
            return;
        }

        // Récupérer l'utilisateur connecté
        $user = $this->security->getUser();

        // Si l'utilisateur n'est pas connecté, on ne fait rien
        if (!$user instanceof User) {
            return;
        }

        // Si l'utilisateur n'a pas complété son profil et n'est pas déjà autorisé temporairement, vérifier la route
        if ($user->getUserProfile() === null) {
            $currentRoute = $event->getRequest()->attributes->get('_route');

            // Redirige uniquement si l'utilisateur essaie d'accéder à une autre page que "app_register_complete" ou "app_logout"
            if ($currentRoute !== 'app_register-complete' && $currentRoute !== 'app_logout') {
                $response = new RedirectResponse($this->router->generate('app_register-complete'));
                $event->setResponse($response);
            }
        }
    }
}
