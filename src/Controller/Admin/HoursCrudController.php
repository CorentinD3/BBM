<?php

namespace App\Controller\Admin;

use App\Entity\Hours;
use App\Entity\OldAppointment;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class HoursCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;


    public static function getEntityFqcn(): string
    {
        return Hours::class;
    }

    // Surcharge de la méthode pour filtrer les Hours avec un User
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {

        // Récupère le QueryBuilder de base
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Ajoute une condition pour filtrer les Hours qui ont un User associé
        $queryBuilder
            ->andWhere('entity.user IS NOT NULL'); // "entity" est l'alias de l'entité Hours dans la requête

        return $queryBuilder;
    }

    // Configuration des champs à afficher dans l'interface d'administration
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('date', 'Date'),
            TimeField::new('hour', 'Heure')
                ->setFormat('HH:mm')
                ->renderAsNativeWidget(true),
            AssociationField::new('prestation', 'Prestation'),
            AssociationField::new('variant', 'Variante'),
            AssociationField::new('user', 'Utilisateur'),

        ];
    }
    public function configureActions(Actions $actions): Actions
    {
        // Créer une action "Annuler le RDV" qui redirige vers la route "app_cancel-appointment"
        $cancelAction = Action::new('cancel', 'Annuler la réservation')
            ->linkToCrudAction('cancelAppointment')
            ->setCssClass(' text-danger hover:btn-danger ');
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $cancelAction);;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Rendez-vous')
            ->setEntityLabelInPlural('Rendez-vous')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des rendez-vous');

    }

    public function cancelAppointment(AdminContext $context, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        // Récupère l'instance Hours à partir du contexte EasyAdmin
        /** @var \App\Entity\Hours $hours */
        $hours = $context->getEntity()->getInstance();

        // Créer une archive dans OldAppointment
        $oldAppointment = new \App\Entity\OldAppointment();
        $oldAppointment->setUser($hours->getUser());
        $oldAppointment->setHour($hours->getHour());
        $oldAppointment->setDate($hours->getDate()->getDate());
        $oldAppointment->setPrestation($hours->getPrestation()?->getLabel());
        if ($hours->getVariant()) {
            $oldAppointment->setVariant($hours->getVariant()->getLabel());
        }
        $oldAppointment->setStatus("canceled");

        $entityManager->persist($oldAppointment);

        // Annuler la réservation en vidant les informations
        $hours->setUser(null);
        $hours->setPrestation(null);
        $hours->setVariant(null);
        $entityManager->persist($hours);

        $entityManager->flush();
        $route = $request->headers->get('referer');

        //$this->addFlash('success', 'La réservation a été annulée avec succès.');
        return $this->redirect($route);
    }
}
