<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\EventSubscriber\CalendarSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    /**
     * @var TaskRepository
     */
    private $repository;

    /**
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * constructeur du Taskcontroller, Type hinté le repository et le manager
     *
     * @param TaskRepository $repository
     * @param EntityManagerInterface $manager
     */
    public function __construct(TaskRepository $repository, EntityManagerInterface $manager){
        $this->repository = $repository;
        $this->manager = $manager;
    }
    
    
    
    /**
     * @Route("/tasks/listing", name="tasks_listing")
     */
    public function taskListing()
    {

        //Recupère les infos de l'utilisateur connecté
        // $user = $this->getUser();
        // dd($user);
        

        //On récupère toutes les tasks du repository
        $tasks= $this->repository->findAll();

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }


     /**
      * @Route("/tasks/create", name="task_create")
      * @Route("/tasks/update/{id}", name="task_update", requirements={"id"="\d+"})
      * @param Request $request
      * @return Response
      */
    public function task(Task $task = null, Request $request): Response
    {

       
        if(!$task){
            $task=new Task();
            $flag= true;
        }
        else{
            $flag= false;
        }
        
        //Génération du formulaire
        $form = $this->createForm(TaskType::class, $task,array());
        
        if($flag)
        {
            $task->setCreatedAt(new \DateTime);
        }


        //Récupérer les données du formulaire
        $form->handleRequest($request);

        //Vérification si formulaire est soumis et données ok
        if ($form->isSubmitted() and $form->isValid()){
            $task->setName($form['name']->getData())
            ->setDescription($form['description']->getData())
            ->setDueAt($form['dueAt']->getData())
            ->setTag($form['tag']->getData());

            //$manager = $this->getDoctrine()->getManager();
            $this->manager->persist($task);
            $this->manager->flush();

            if($flag){
                $this->addFlash('success', 'Votre tâche a été ajoutée.');
            }
            else{
                $this->addFlash('success', 'La modification est prise en compte.');
            }

            return $this->redirectToRoute('tasks_listing');
        }

        return $this->render('task/create.html.twig', array(
            'task'=>$task,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/tasks/delete/{id}", name="task_delete", requirements={"id"="\d+"})
     *
     * @param Task $task
     * 
     */
    public function deleteTask(Task $task): Response
    {
       
        $this->manager->remove($task);
        $this->manager->flush();

        $this->addFlash('warning', 'Tâche supprimée.');

        return $this->redirectToRoute('tasks_listing');
    }

}

