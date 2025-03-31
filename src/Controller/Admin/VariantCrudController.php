<?php

namespace App\Controller\Admin;

use App\Entity\Variant;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class VariantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Variant::class;
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
            AssociationField::new('prestation', 'Prestations') // Relation avec Variant
            ->autocomplete() // Permet la recherche automatique
             ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Remplissage')
            ->setEntityLabelInPlural('Remplissages')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des remplissages')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modification d\'un remplissage')
            ->setPageTitle(Crud::PAGE_NEW, 'Création d\'un remplissage');
    }

}
