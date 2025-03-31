<?php

namespace App\Controller\Admin;

use App\Entity\Date;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class DateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Date::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(), // Cache l'ID dans le formulaire car il est généré automatiquement
            DateField::new('date', 'Date')
                ->setFormat('dd/MM/yyyy'), // Format de la date
            CollectionField::new('hours', 'Heures associées')
                ->useEntryCrudForm(HoursCrudController::class), // Permet de gérer les heures associées via le CRUD des heures
        ];
    }
}