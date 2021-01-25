<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Form\CompositionType;
use App\Entity\CategoryRecipe;
use App\Entity\CategoryIngredient;
use App\Entity\Composition;
use App\Entity\Ingredient;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
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
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez une description de votre recette',
                    ]),
                ],
            ])
            ->add('cookingTime', IntegerType::class, [
                'label' => 'temps de cuisson (en minutes) ',
            ])
            ->add('preparationTime', IntegerType::class, [
                'label' => 'temps de préparation (en minutes) :  ',
            ])
            ->add('instructions', CKEditorType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer des instructions',
                    ]),
                ],
            ])
            ->add('nbPerson', IntegerType::class, [
                'attr' => array('min' => 1, 'max' => 20), 
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez mettre pour combien de personne est cette recette',
                    ]),
                ],
            ])
            
            ->add('categoryIngredient', EntityType::class,[
                'class' => CategoryIngredient::class,
                'mapped' => false,
                'choice_label' => 'name'
            ])
            ;
            // ->add('compositions', CollectionType::class, [
            //     'entry_type' => CompositionType::class,
            //     'entry_options' => [
                   
            //         'label' => 'ajouter des ingredients',
            //     ],
            //     'allow_add' => true,
            //     'label' => false,
            //     'allow_delete' => true,
            //     'by_reference' => false
            // ])

            
            $formModifier = function (FormInterface $form, CategoryIngredient $categoryIngredient) {
                $ingredients = null === $categoryIngredient ? [] : $categoryIngredient->getIngredients(); //->getCompostions();


                $form->add('compositions', CollectionType::class, [
                    'entry_type' => CompositionType::class,
                    'entry_options' => [
                    
                        'label' => 'ajouter des ingredients',
                    ],
                    // 'class' => 'App\Entity\Composition',
                    'choices' => $ingredients,
                    'allow_add' => true,
                    'label' => false,
                    'allow_delete' => true,
                    'by_reference' => false
                ]);
            };

            $builder->addEventListener(
                //PRE_SET permet : pour modifier les donnees pre-envoie
                //garde synchronisation du form sur la donnée choisie
                //FormEvent donne un procédé pour modifier son form dynamiquement
                FormEvents::PRE_SET_DATA,// ? 
                function (FormEvent $event) use ($formModifier) {
                    // deviendra la recette
                    $data = $event->getData();
    
                    $formModifier($event->getForm(), $data->getCategoryIngredient());
                }
            );

            $builder->get('categoryIngredient')->addEventListener(
                //permet l'affichage form modifié
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    //methode fetch qui permet d'avoir l'id de la catégorie d'ingrédient
                    $categoryIngredient = $event->getForm()->getData();
    
                    //une fois le listener ajouté et le choix d'ingrédients prit en compte
                    //on passe l'info à l'autre fonction d'ajout au formulaire
                    $formModifier($event->getForm()->getParent(), $categoryIngredient);
                }
            )

            ->add('categories', CollectionType::class, [
                'label' => false,
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => CategoryRecipe::class,
                    'choice_label' => 'name',
                    'label' => false
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
            ->add('Valider', SubmitType::class)
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {   
        // $defaults = array(
        //     'compound' => true,
        //     'inherit_data' => true,
        // );
        
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
