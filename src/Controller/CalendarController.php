<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    /**
     * @Route("/calendar", name="calendar", methods={"GET"})
     *
     * @return Response
     */
    public function loadCalendar(): Response
    {

        return $this->render('calendar/index.html.twig');
    }
}
