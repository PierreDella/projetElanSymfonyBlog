<?php

namespace App\Form;

use App\Entity\CategoryIngredient;
use App\Entity\Recipe;
use App\Form\CompositionType;
use App\Entity\CategoryRecipe;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        // ->add('valider', SubmitType::class)
            ->add('name', TextType::class, [
                'label' => 'Titre ',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom de recette',
                    ]),
                    ],
            ])
        
            ->add('picture', FileType::class, [
                'label' => 'photo ',
                'mapped' => false, 
                'multiple' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k'
                    ])
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez une description de votre recette',
                    ]),
                ],
            ])
            ->add('cookingTime', IntegerType::class, [
                'label' => 'temps de cuisson (en minutes) ',
                'attr' => array('min' => 0),
                'required' => false,
            ])
            ->add('preparationTime', IntegerType::class, [
                'label' => 'temps de préparation (en minutes) :  ',
                'attr' => array('min' => 0),
            ])
            ->add('instructions', CKEditorType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer des instructions',
                    ]),
                ],
            ])
            ->add('nbPerson', IntegerType::class, [
                'label' => 'Nombre de personne',
                'attr' => array('min' => 1, 'max' => 20), 
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez mettre pour combien de personne est cette recette',
                    ]),
                ],
            ])
            // ->add('catIngredient', EntityType::class,[
            //     'class' => CategoryIngredient::class,
            //     'mapped' => false,
            //     'choice_label' => 'name'
            // ])
            ->add('compositions', CollectionType::class, [
                'required' => true,
                'entry_type' => CompositionType::class,
                'entry_options' => [
                   
                    'label' => 'ajouter des ingredients',
                ],
                'allow_add' => true,
                'label' => false,
                'allow_delete' => true,
                'by_reference' => false
            ])
            ->add('categories', CollectionType::class, [
                // 'label_html' => 'choisir',  
                // 'prototype_data' => 'New Tag Placeholder',   

                // 'label' => 'choisir une catégorie   ',
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => CategoryRecipe::class,
                    'choice_label' => 'name',
                    'label' => false
                ],
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('Valider', SubmitType::class)
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
