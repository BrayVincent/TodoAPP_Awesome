<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Task;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => 'Nom de la tâche', 'attr' => array(
                'class' => 'form-control',
                'title' => 'Nom de la tâche',
            )))
            ->add('description', TextareaType::class, array('label' => 'Description', 'attr' => array(
                'class' => 'form-control',
                'title' => 'Description',
            )))
            ->add('startAt', DateTimeType::class, array('widget' => 'single_text', 'label' => "Date de début", 'attr' => array(
                'class' => 'form-control',
                'title' => "Date de début"
            )))
            ->add('dueAt', DateTimeType::class, array('widget' => 'single_text', 'label' => 'Date effective', 'attr' => array(
                'class' => 'form-control',
                'title' => 'Date effective',
            )))
            ->add('tag', EntityType::class, [
                'class' => Tag::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'attr' => array(
                    'class' => 'form-control',
                    'title' => 'Catégorie',
                )
            ])
            ->add('save', SubmitType::class, array(
                'label' => 'Enregistrer',
                'attr' => array(
                    'class' => 'btn btn-primary',
                    'title' => 'Enregistrer'
                )
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
