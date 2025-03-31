<?php

namespace App\Controller\Admin;

use App\Entity\Hours;
use App\Entity\OldAppointment;
use App\Entity\Prestations;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Entity\Variant;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Locale;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    #[isGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            // the name visible to end users
            // you can include HTML contents too (e.g. to link to an image)
            ->setTitle('<span class="text-small">Beauty by M.</span>')

            // by default EasyAdmin displays a black square as its default favicon;
            // use this method to display a custom favicon: the given path is passed
            // "as is" to the Twig asset() function:
            // <link rel="shortcut icon" href="{{ asset('...') }}">
            ->setFaviconPath('favicon.svg')



            // set this option if you prefer the page content to span the entire
            // browser width, instead of the default design which sets a max width
            ->renderContentMaximized()



            // by default, the UI color scheme is 'auto', which means that the backend
            // will use the same mode (light/dark) as the operating system and will
            // change in sync when the OS mode changes.
            // Use this option to set which mode ('light', 'dark' or 'auto') will users see
            // by default in the backend (users can change it via the color scheme selector)
            // instead of magic strings, you can use constants as the value of
            // this option: EasyCorp\Bundle\EasyAdminBundle\Config\Option\ColorScheme::DARK
            ->disableDarkMode()

            ->setLocales([
                'fr' => 'France',
            ]);
    }

    public function configureMenuItems(): iterable
    {
        // Lien vers le tableau de bord
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // Section pour les prestations
        yield MenuItem::section('Prestations');

        // Lien vers le CRUD des prestations
        yield MenuItem::linkToCrud('Prestation', 'fa fa-tags', Prestations::class);

        // Lien vers le CRUD des variants
        yield MenuItem::linkToCrud('Remplissage', 'fa fa-file-text', Variant::class);

        yield MenuItem::section('Utilisateur');

        // Lien vers le CRUD des utilisateurs
        yield MenuItem::linkToCrud('Client', 'fa fa-tags', UserProfile::class);

        yield MenuItem::section('Rendez-vous');

        yield MenuItem::linkToCrud('Rendez-vous', 'fa fa-tags', Hours::class);

        yield MenuItem::linkToRoute('Calendrier', 'fa fa-calendar', 'app_admin-calendar');

    }

}
