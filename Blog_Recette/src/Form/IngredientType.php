<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\CategoryIngredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom',
                    ]),
                    ],
            ])
            ->add('category', EntityType::class, [
                'class' =>CategoryIngredient::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez choisir une catégorie pour l'ingredient",
                    ]),
                    ],
            ])
                    
            ->add('Valider', SubmitType::class)
            ->getForm();
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
        ]);
    }
}
