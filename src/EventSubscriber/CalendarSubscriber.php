<?php

namespace App\EventSubscriber;

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

        // Modify the query to fit to your entity and needs
        $tasks = $this->manager->getRepository(Task::class)
            ->createQueryBuilder('c')
            ->where('c.startAt BETWEEN :start and :end OR c.dueAt BETWEEN :start and :end')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();

        foreach ($tasks as $task) {
            //Créer un évenement pour chaque tâche
            $taskEvent = new Event(
                $task->getName(), //Titre
                $task->getStartAt(), //Date de début
                $task->getDueAt() // Date de fin        
            );


            //Option pour les couleurs des tâches

            $taskEvent->setOptions([
                'backgroundColor' => 'blue',
                'borderColor' => 'blue',
            ]);

            // Ajouter les évènements au calendrier
            $calendar->addEvent($taskEvent);
        }
    }
}
