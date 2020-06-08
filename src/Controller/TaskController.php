<?php

namespace App\Controller;

use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks/listing", name="tasks_listing")
     */
    public function taskListing()
    {

        // On va chercher par doctrine le repository de nos Task
        $repository = $this->getDoctrine()->getRepository(Task::class);

        // dans ce repository nous récupérons toutes les données
        $tasks = $repository->findAll();

        // affichage des données dans var_dumper
        dd($tasks);

        return $this->render('task/index.html.twig', [
            'controller_name' => 'TaskController',
        ]);
    }
}
