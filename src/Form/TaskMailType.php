<?php
namespace App\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class TaskMailType extends AbstractType
{
 public function buildForm(FormBuilderInterface $builder, array $options)
 {
 $builder
 ->add('email', EmailType::class, array('label' => "Veuillez saisir l'adresse de
destination :",
 'attr' => array('class' => 'form-control',
 'title' => 'Destinataire')))
 ->add('save', SubmitType::class, array('label' => 'Envoyer', 'attr' => array('c
lass' => 'btn btn-outline-primary', 'title' => 'Envoyer')));
 }

}