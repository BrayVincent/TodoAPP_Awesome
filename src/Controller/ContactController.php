<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactController extends AbstractController
{
    /**
     * @var TaskRepository
     */

    private $repository;
    /**
     * Undocumented variable
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Construct du TaskController, Type Hinté le repository et le manager
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
     * @Route("/contact", name="task_mail", requirements={"id"="\d+"})
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function sendTask(\Swift_Mailer $mailer): Response
    {
        //récupérer les infos de l'utilisateur
        $user = $this->getUser();
        //Récupère la tâche par son id
        $id = $_GET['id'];
        $task = $this->repository->findOneById($id);

        //Envoi du mail
        $message = (new \Swift_Message('Voici votre tâche'))
            ->setFrom($_SERVER['MAILER_ADDRESS'])
            ->setTo($user->getEmail())
            ->setBody($this->renderView('mail/task.html.twig', ['task' => $task]), 'text/html');
        $mailer->send($message);
        $this->addFlash(
            'success',
            $this->translator->trans('task.mail.success')
        );

        return $this->redirectToRoute('tasks_listing');
    }
}
