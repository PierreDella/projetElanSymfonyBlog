<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\CategoryRecipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom de recette',
                    ]),
                    ],
            ])
            ->add('picture', FileType::class, [
                'label' => 'photoProfil ',
                'mapped' => false
            ])
            ->add('description', TextType::class, [
            ])
            ->add('cookingTime')
            ->add('preparationTime')
            ->add('steps')
            ->add('instructions')
            ->add('nbPerson')
            ->add('categories', EntityType::class, [
                'class' => CategoryRecipe::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez choisir une catégorie',
                    ]),
                    ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
