<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        // dd($tasks);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     * @param Request $request
     * @return Response
     */
    public function createTask(Request $request): Response
    {
        // On crée un objet Task
        $task = new Task();

        $task->setCreatedAt(new \DateTime);

        // On crée notre formulaire
        $form = $this->createForm(TaskType::class, $task, array());

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }
}
