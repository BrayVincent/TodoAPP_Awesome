<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CalendarController extends AbstractController
{
    
    /**
     * @Route("/calendar", name="calendar", methods={"GET"})
     *
     * @return Response
     */
    public function loadCalendar(): Response{    

        return $this->render('calendar/index.html.twig');

    }

    /** 
    * @Route("/ajax", name="ajax", methods={"GET","POST"})
    */ 
    public function ajaxAction(Request $request): Response {  
        //dd($request);
        $data = json_decode($request->getContent(), true);
        dd($data);

    } 
}
