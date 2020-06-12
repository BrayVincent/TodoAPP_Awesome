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

    //Avoir les couleurs par tâche
    public function tagNameById(Task $task)
    {
        $taskId = $task->getTag();
        $tag = $this->manager->getRepository(Tag::class);
        $tagId = $tag->findOneBy(['id' => $taskId]);
        $color = $tagId->getName();
        return $color;
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
            //Afficher les couleuur pour chaque tâche
            $color = $this->tagNameById($task);
            //Créer un évenement pour chaque tâche
            $taskEvent = new Event(
                $task->getName(), //Titre
                $task->getStartAt(), //Date de début
                $task->getDueAt() // Date de fin,If the end date is null or not defined, a all day event is created.
            );



            //Option pour les couleurs des tâches

            $taskEvent->setOptions([
                'backgroundColor' => $color,
                'borderColor' => $color,
            ]);

            // Ajouter les évènements au calendrier
            $calendar->addEvent($taskEvent);
        }
    }
}
