<?php

namespace App\Controller\Admin;

use App\Entity\OldAppointment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class OldAppointmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OldAppointment::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('date', 'Date'),
            TimeField::new('hour', 'Heure')
                ->setFormat('HH:mm')
                ->renderAsNativeWidget(true),
            TextField::new('prestation', 'Prestation'),
            TextField::new('variant', 'Variante'),
            TextField::new('status', 'Statut'),
            AssociationField::new('user', 'Client   '),

        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Historique')
            ->setEntityLabelInPlural('Historiques')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion de l\'historique');

    }

}
