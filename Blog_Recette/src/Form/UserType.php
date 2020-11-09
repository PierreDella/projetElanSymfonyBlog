<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entre un email'
                    ]),
                    ],
            ])
            ->add('pseudo', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre pseudo',
                    ]),
                ],
            ])
            ->add('picture', FileType::class, [
                'label' => 'photoProfil ',
                'mapped' => false
            ])
            ->add('dateNaissance', DateType::class,[
                "widget" => 'single_text',
                'label' => 'Date de naissance',
                'constraints' => [
                    new LessThan([
                        'value' => 'now',
                        'message' => 'La date doit être inférieure à celle du jour.'
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez entrer une date',
                    ]),
                ],
            
            ])
            ->add('Valider', SubmitType::class)
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
