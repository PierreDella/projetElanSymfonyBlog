<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\CategoryRecipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('valider', SubmitType::class)
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom de recette',
                    ]),
                    ],
            ])
            // ->add('picture', FileType::class, [
            //     'label' => 'photo ',
            //     'mapped' => false
            // ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez une description de votre recette',
                    ]),
                ],
            ])
            // ->add('cookingTime')
            // ->add('preparationTime')
            // ->add('steps')
            ->add('instructions', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer des instructions',
                    ]),
                ],
            ])
            ->add('nbPerson', IntegerType::class, [
                'attr' => array('min' => 1), 
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez mettre pour combien de personne est cette recette',
                    ]),
                ],
            ])
            ->add('categories', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => CategoryRecipe::class,
                    'choice_label' => 'name',
                    'label' => false
                ],
                'allow_add' => true,
                'by_reference' => false
                // 'constraints' => [
                //     new NotBlank([
                //         'message' => 'Veuillez choisir au moins une catégorie',
                //     ]),
                //     ],
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
