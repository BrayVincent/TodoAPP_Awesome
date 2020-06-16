<?php

namespace App\Controller;

use Dompdf\Dompdf;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{

    /**
     * @var TaskRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructeur du TaskController, Type Hinté le repository et le manager
     *
     * @param TaskRepository $repository
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     */
    public function __construct(TaskRepository $repository, EntityManagerInterface $manager, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->manager = $manager;
        $this->translator = $translator;
    }

    /**
     * @Route("/tasks/listing", name="tasks_listing")
     */
    public function taskListing()
    {

        // Récupérer les informations de l'utilisateur connecté
        // $user = $this->getUser();
        // dd($user);

        // dans ce repository nous récupérons toutes les données
        $tasks = $this->repository->findAll();

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

        if (!$task) {
            $task = new Task();
            $flag = true;
        } else {
            $flag = false;
        }

        // On crée notre formulaire
        $form = $this->createForm(TaskType::class, $task, array());

        if ($flag) {
            $task->setCreatedAt(new \DateTime);
        }

        // On récupère notre formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $task->setName($form['name']->getData())
                ->setDescription($form['description']->getData())
                ->setDueAt($form['dueAt']->getData())
                ->setTag($form['tag']->getData());

            // On fait persister notre task
            $this->manager->persist($task);
            // On flush le tout en BDD
            $this->manager->flush();

            if ($flag) {
                $this->addFlash(
                    'success',
                    $this->translator->trans('flash.tache.ajout')
                );
            } else {
                $this->addFlash(
                    'success',
                    $this->translator->trans('flash.tache.modif')
                );
            }

            return $this->redirectToRoute('tasks_listing');
        }

        return $this->render('task/create.html.twig', ['task' => $task, 'form' => $form->createView()]);
    }

    /**
     * @Route("tasks/delete/{id}", name="task_delete", requirements={"id"="\d+"})
     *
     * @param Task $task
     * @return Response
     */
    public function deleteTask(Task $task): Response
    {
        $this->manager->remove($task);
        $this->manager->flush();

        $this->addFlash(
            'warning',
            $this->translator->trans('flash.tache.del')
        );

        return $this->redirectToRoute('tasks_listing');
    }

    /**
     * Méthode pour générer un PDF à partir de la liste des tâches *
     * @Route("/tasks/pdf",name="tasks_pdf")
     */
    public function pdf()
    {
        // On va récupérer les tâches 
        $tasks = $this->repository->findAll();

        // On crée de l'objet dompdf 
        $dompdf = new Dompdf();

        // On génère le contenu à partir du fichier twig 
        $html = $this->renderView('pdf/task.html.twig', ['tasks' => $tasks]);

        // On le charge dans le pdf 
        $dompdf->loadHtml($html);

        // On souhaite un format A4 en mode portrait (vertical) 
        // pour un mode paysage saisir : 'landscape' 
        $dompdf->setPaper('A4', 'portrait');

        // Obtenir le rendu 
        $dompdf->render();

        // On souhaite que le pdf s'ouvre dans le navigateur (sans téléchargement immédiat) 
        // pour un téléchargement direct (changer false par true) 
        // "tasks_listing.pdf" sera le nom par défaut lors de l'enregistrement du pdf 
        $dompdf->stream("tasks_listing.pdf", ["Attachment" => false]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        //si on est connecté, le lien home renvoie sur la liste des taches, sinon sur l'écran
        if ($this->getUser()) {
            return $this->redirectToRoute('tasks_listing');
        }
        return $this->render('home.html.twig');
    }
}
