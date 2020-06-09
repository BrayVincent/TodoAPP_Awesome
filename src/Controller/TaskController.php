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

        // On récupère notre formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $task->setName($form['name']->getData())
                ->setDescription($form['description']->getData())
                ->setDueAt($form['dueAt']->getData())
                ->setTag($form['tag']->getData());

            // On va chercher notre manager
            $manager = $this->getDoctrine()->getManager();
            // On fait persister notre task
            $manager->persist($task);
            // On flush le tout en BDD
            $manager->flush();

            return $this->redirectToRoute('tasks_listing');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }


    /**
     * @Route("/tasks/update/{id}", name="task_update", requirements={"id"="\d+"})
     * @param [type] $id
     * @param Request $request
     * @return Response
     */
    public function updateTask($id, Request $request): Response
    {
        // On va chercher en BDD la tâche qui correspond à l'ID passé en paramètre
        $task = $this->getDoctrine()->getRepository(Task::class)->findOneBy(['id' => $id]);

        $form = $this->createForm(TaskType::class, $task, array());

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $task->setName($form['name']->getData())
                ->setDescription($form['description']->getData())
                ->setDueAt($form['dueAt']->getData())
                ->setTag($form['tag']->getData());

            $manager->persist($task);
            $manager->flush();

            return $this->redirectToRoute('tasks_listing');
        }

        return $this->render('task/create.html.twig', array('task' => $task, 'form' => $form->createView()));
    }
}
