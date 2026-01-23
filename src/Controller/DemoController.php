<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    #[Route('/demo', name: 'demo_index')]
    public function index(): Response
    {
        // FR "manuel", pas d'intl, pas de Twig
        $jours = [
            'Sunday' => 'Dimanche',
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
        ];
        $mois = [
            'January' => 'janvier',
            'February' => 'février',
            'March' => 'mars',
            'April' => 'avril',
            'May' => 'mai',
            'June' => 'juin',
            'July' => 'juillet',
            'August' => 'août',
            'September' => 'septembre',
            'October' => 'octobre',
            'November' => 'novembre',
            'December' => 'décembre',
        ];

        $dt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        $h1 = sprintf(
            '%s %d %s %d',
            $jours[$dt->format('l')],
            (int) $dt->format('j'),
            $mois[$dt->format('F')],
            (int) $dt->format('Y')
        );

        return new Response('<h1 style="font-family:system-ui">' . $h1 . '</h1>');
    }
}
