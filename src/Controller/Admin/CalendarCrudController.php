<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CalendarCrudController extends AbstractController
{

    #[Route('/admin/calendar', name: 'app_admin-calendar')]
    public function adminCal(): Response
    {
        // Vous pouvez passer des variables au template si nécessaire
        return $this->render('admin/calendar.html.twig', [

        ]);
    }
}