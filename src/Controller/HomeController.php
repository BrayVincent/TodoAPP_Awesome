<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
    * @Route("/", name="home")
    */
    public function home(){
        //si on est connecté, le lien home renvoie sur la liste des taches, sinon sur l'écran
        if ($this->getUser()) {
            return $this->redirectToRoute('tasks_listing');
        }
        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/home", name="app_home")
     */
    public function app_home(){
        return $this->render('home.html.twig');
    }
}

