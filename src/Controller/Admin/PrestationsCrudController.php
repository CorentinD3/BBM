<?php

namespace App\Controller\Admin;

use App\Entity\Prestations;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class PrestationsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Prestations::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('label', 'Label'), // Champ texte pour le label
            TimeField::new('duration', 'Durée') // Champ temps pour la durée
            ->setFormat('HH:mm'), // Format d'affichage
            MoneyField::new('price', 'Prix') // Champ monétaire pour le prix
            ->setCurrency('EUR') // Devise (ajustez selon vos besoins)
            ->setStoredAsCents(false), // Stocker en euros, pas en centimes

        ];
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Prestation')
            ->setEntityLabelInPlural('Prestations')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des prestations')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modification d\'une prestation')
            ->setPageTitle(Crud::PAGE_NEW, 'Création d\'une prestation');
    }
}