<?php

namespace App\Controller\Admin;

use App\Entity\BanList;
use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserProfileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserProfile::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
        // Champs du UserProfile
        yield TextField::new('firstName', 'Prénom'),
        yield TextField::new('lastName', 'Nom'),

        // Champs du User associé (accès via la propriété user.*)
        // Attention : la property_path "user.number" suppose un getter/setter getNumber()/setNumber() dans User
        yield TextField::new('user.number', 'Telephone'),
        yield EmailField::new('email', 'Email'),

        // Exemple pour les rôles
        yield ChoiceField::new('user.roles', 'Rôles')
            ->setChoices([
                'Administrateur' => 'ROLE_ADMIN',
                'Utilisateur'    => 'ROLE_USER',
            ])
            ->allowMultipleChoices(true)
            ->renderExpanded(true),
        yield TextField::new('user.banList', 'Ban')
            ->hideOnForm()
        ];
    }

    /**
     * Gère la création d'un UserProfile + User.
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof UserProfile) {
            return;
        }

        // Récupération ou création du User
        $user = $entityInstance->getUser();
        if (null === $user) {
            $user = new User();
            $entityInstance->setUser($user);
        }

        // On peut faire ici de la logique supplémentaire :
        // - Génération d’un numéro unique
        // - Validation particulière, etc.

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * Gère la mise à jour d'un UserProfile + User.
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof UserProfile) {
            return;
        }

        // Ici aussi, on peut manipuler la logique pour le User
        $user = $entityInstance->getUser();
        if (null === $user) {
            $user = new User();
            $entityInstance->setUser($user);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function createEntity(string $entityFqcn): UserProfile
    {
        $userProfile = new UserProfile();
        $userProfile->setUser(new User());
        return $userProfile;
    }

    public function configureActions(Actions $actions): Actions
    {
        // Action "ban"
        $ban = Action::new('ban', 'Ban', 'fas fa-ban')
            ->linkToCrudAction('banUser')
            // On n'affiche ce bouton que si l'utilisateur n'est pas encore banni
            ->displayIf(static function (UserProfile $profile) {
                // Récupère l'utilisateur lié
                $user = $profile->getUser();
                // Vérifie si user non null ET pas de banList
                return $user && null === $user->getBanList();
            });

        // Action "unban"
        $unban = Action::new('unban', 'Unban', 'fas fa-user-check')
            ->linkToCrudAction('unbanUser')
            // On n'affiche ce bouton que si l'utilisateur est banni
            ->displayIf(static function (UserProfile $profile) {
                $user = $profile->getUser();
                return $user && null !== $user->getBanList();
            });

        return $actions
            ->disable(Action::NEW)
        // Ajout de l’action sur la page de liste (index)
            ->add(Crud::PAGE_INDEX, $ban)
            ->add(Crud::PAGE_INDEX, $unban)

            // Si besoin, vous pouvez aussi les ajouter sur la page d'édition
            ->add(Crud::PAGE_EDIT, $ban)
            ->add(Crud::PAGE_EDIT, $unban);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Client')
            ->setEntityLabelInPlural('Clients')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des clients')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modification d\'un client')
            ->setPageTitle(Crud::PAGE_NEW, 'Création d\'un client');
    }

    public function banUser(AdminContext $context, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        /** @var UserProfile $profile */
        $profile = $context->getEntity()->getInstance();

        if (!$profile instanceof UserProfile) {
            $this->addFlash('warning', 'Impossible de bannir : entité invalide.');
            return $this->redirectToRoute($context->getReferrer());
        }

        $user = $profile->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Cet utilisateur n\'existe pas.');
            return $this->redirectToRoute($context->getReferrer());
        }

        // Vérifie si l'utilisateur est déjà banni
        if (null !== $user->getBanList()) {
            $this->addFlash('warning', 'Cet utilisateur est déjà banni.');
            return $this->redirectToRoute($context->getReferrer());
        }

        // Crée et persiste une nouvelle entité BanList
        $banList = new BanList();
        $banList->setUser($user);
        $entityManager->persist($banList);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur banni avec succès.');
        return $this->redirect(
            $context->getReferrer()
            ?? $adminUrlGenerator->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }

    public function unbanUser(AdminContext $context, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        /** @var UserProfile $profile */
        $profile = $context->getEntity()->getInstance();

        if (!$profile instanceof UserProfile) {
            $this->addFlash('warning', 'Impossible de débannir : entité invalide.');
            return $this->redirectToRoute($context->getReferrer());
        }

        $user = $profile->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Utilisateur introuvable.');
            return $this->redirectToRoute($context->getReferrer());
        }

        // Vérifie si l'utilisateur est banni
        $banList = $user->getBanList();
        if (null === $banList) {
            $this->addFlash('warning', 'Cet utilisateur n\'est pas banni.');
            return $this->redirectToRoute($context->getReferrer());
        }

        // Supprime l'entrée BanList
        $entityManager->remove($banList);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur débanni avec succès.');
        return $this->redirect(
            $context->getReferrer()
            ?? $adminUrlGenerator->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }
}
