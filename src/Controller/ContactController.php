<?php
namespace App\Controller;
use App\Entity\Task;
use App\Form\TaskType;
use App\Form\TaskMailType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
 * Construct du TaskController, Type Hinté le repository et le manager
 *
 * @param TaskRepository $repository
 * @param EntityManagerInterface $manager
 */
 public function __construct(TaskRepository $repository, EntityManagerInterface $manager){
 $this->repository = $repository;
 $this->manager = $manager;
 }
 /**
 * @Route("/contact", name="task_mail", requirements={"id"="\d+"})
 * @param \Swift_Mailer $mailer
 * @return Response
 */
public function sendTask(Task $task = null, \Swift_Mailer $mailer, Request $request) : Response
 {
    //récupérer les infos de l'utilisateur
    $user=$this->getUser();
    //on récupère l'adresse mail de l'utilisateur
    $usermail = $user->getEmail();
    //on crée notre formulaire
    $form = $this->createForm(TaskMailType::class,array('email'=>$usermail));
    // On récupère notre formulaire
    $form->handleRequest($request);
    //Récupère la tâche par son id
    $id= $_GET['id'];
    $task = $this->repository->findOneById($id);
    //on vérifie si le formulaire est soumi
    if($form->isSubmitted() and $form->isValid()){
        //on récupère la valeur de notre input email
        $adress=$form->get('email')->getData();

        //Envoi du mail
        $message = (new \Swift_Message('Voici votre tâche'))
        ->setFrom($_SERVER['MAILER_ADDRESS'])
        ->setTo($adress)
        ->setBody($this->renderView('mail/task.html.twig', ['task' => $task]),'text/html');
        $mailer->send($message);
        $this->addFlash(
        'success',
        'Vous avez bien envoyé votre tâche !'
        );
        return $this->redirectToRoute('tasks_listing');
    }

    // return $this->redirectToRoute('tasks_listing');
    return $this->render('mail/formSendTo.html.twig', ['task'=>$task, 'form'=> $form->createView()]);
    }

}
