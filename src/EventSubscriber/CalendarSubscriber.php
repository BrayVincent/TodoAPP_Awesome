<?php

namespace App\EventSubscriber;

use App\Entity\Tag;
use App\Entity\Task;
use CalendarBundle\Entity\Event;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Event\CalendarEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class CalendarSubscriber implements EventSubscriberInterface
{
    private $manager;
    private $router;
    private $tagController;

    public function __construct(
        EntityManagerInterface $manager, 
        UrlGeneratorInterface $router

    ) {
        $this->manager = $manager;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            CalendarEvents::SET_DATA => 'load',
        ];
    }

    public function load(CalendarEvent $calendar)
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        //$filters = $calendar->getFilters();

        // Modify the query to fit to your entity and needs
        $tasks = $this->manager->getRepository(Task::class)
            ->createQueryBuilder('c')
            ->where('c.startAt BETWEEN :start and :end OR c.dueAt BETWEEN :start and :end')
            ->setParameter('start', $start->format('Y-m-d H:i'))
            ->setParameter('end', $end->format('Y-m-d H:i'))
            ->getQuery()
            ->getResult()
        ;

        foreach ($tasks as $task) {
            //Afficher les couleuur pour chaque tâche
            $color=$task->getTag()->getColor();
            //Créer un évenement pour chaque tâche
            $taskEvent = new Event(
                $task->getName(), //Titre
                $task->getStartAt(), //Date de début
                $task->getDueAt(), // Date de fin
            );
           
            /*
             * Add custom options to events
             *
             * For more information see: https://fullcalendar.io/docs/event-object
             * and: https://github.com/fullcalendar/fullcalendar/blob/master/src/core/options.ts
             */
            
            //Option pour les couleurs des tâches
            $taskEvent->setOptions([
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor'=> 'black',
            ]);
            $taskEvent->addOption(
                'description',
                $task->getDescription()
            );
            $taskEvent->addOption(
                'update',
                $this->router->generate('task_update',['id'=>$task->getId(),])
            );
            $taskEvent->addOption(
                'delete',
                $this->router->generate('task_delete',['id'=>$task->getId(),])
            );
            

            // Ajouter les évênements au calendrier
            $calendar->addEvent($taskEvent);

        }
      
    }
}