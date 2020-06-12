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
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskType extends AbstractType
{

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => $this->translator->trans('task.form.label.name'), 'attr' => array(
                'class' => 'form-control',
                'title' => $this->translator->trans('task.form.label.name'),
            )))
            ->add('description', TextareaType::class, array('label' => $this->translator->trans('task.form.label.description'), 'attr' => array(
                'class' => 'form-control',
                'title' => $this->translator->trans('task.form.label.description'),
            )))
            ->add('startAt', DateTimeType::class, array('widget' => 'single_text', 'label' => $this->translator->trans('task.form.label.startAt'), 'attr' => array(
                'class' => 'form-control',
                'title' => $this->translator->trans('task.form.label.startAt')
            )))
            ->add('dueAt', DateTimeType::class, array('widget' => 'single_text', 'label' => $this->translator->trans('task.form.label.dueAt'), 'attr' => array(
                'class' => 'form-control',
                'title' => $this->translator->trans('task.form.label.dueAt'),
            )))
            ->add('tag', EntityType::class, [
                'class' => Tag::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
                'choice_label' => 'name',
                'label' => $this->translator->trans('task.form.label.tag'),
                'attr' => array(
                    'class' => 'form-control',
                    'title' => $this->translator->trans('task.form.label.tag'),
                )
            ])
            ->add('save', SubmitType::class, array(
                'label' => $this->translator->trans('general.form.save'),
                'attr' => array(
                    'class' => 'btn btn-primary',
                    'title' => $this->translator->trans('general.form.save')
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
