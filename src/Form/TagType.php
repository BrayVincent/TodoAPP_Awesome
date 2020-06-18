<?php

namespace App\Form;

use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TagType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructeur du TagController pour injection de dépendances 
     
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {

        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', TextType::class, array('label' =>'Nom du Tag', 'attr' => array(
            'class'=>'form-control',
            'title' => 'Nom du Tag',
        )))
        ->add('color', ColorType::class, array('label' => $this->translator->trans('tag.form.label.color'), 'attr' => array(
            'class' => 'form-control',
            'title' => $this->translator->trans('listing.thead.couleur'),
        )))

        ->add('save', SubmitType::class, array(
            'label'=>'Enregistrer',
            'attr' => array(
                'class' => 'btn btn-primary',
                'title' => 'Enregistrer'
            )
        ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }
    
}
