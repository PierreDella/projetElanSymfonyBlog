<?php

namespace App\Form;


use App\Entity\Bibliotheque;
use App\Entity\Recipe;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class BibliothequeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer un titre'
                    ]),
                    ],
            ])
            ->add('recipes', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Recipe::class,
                    'choice_label' => 'name',
                    'label' => false
                ],
                'allow_add' => true,
                'by_reference' => false
            ])
            ->add('Valider', SubmitType::class)
            ->getForm();
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Bibliotheque::class,
        ]);
    }
}
