<?php

namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
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
            ->add(
                'email',
                EmailType::class,
                array(
                    'label' => $this->translator->trans('user.form.email'),
                    'attr' => array(
                        'class' => 'form-control',
                        'title' => $this->translator->trans('user.form.email')
                    )
                )
            )
            ->add(
                'password',
                RepeatedType::class,
                array(
                    'type' => PasswordType::class,
                    'invalid_message' => $this->translator->trans('user.form.not_match'),
                    'options' => ['attr' => array(
                        'class' => 'form-control',
                        'title' => $this->translator->trans('user.form.password')
                    )],
                    'required' => true,
                    'first_options'  => ['label' => $this->translator->trans('user.form.password'),
                        //'error_bubbling' => true,
                    ],
                    'second_options' => ['label' => $this->translator->trans('user.form.password_repeat')],
                )
            )
            ->add(
                'save',
                SubmitType::class,
                array(
                    'label' => 'Envoyer',
                    'attr' => array(
                        'class' => 'btn btn-primary',
                        'title' => $this->translator->trans('general.btn.send')
                    )
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
