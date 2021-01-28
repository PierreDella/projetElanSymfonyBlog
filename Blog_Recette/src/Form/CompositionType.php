<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\Composition;
use App\Entity\CategoryIngredient;
use Doctrine\DBAL\Types\StringType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CompositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
        
        ->add('categoryIngredient', EntityType::class,[
            'class' => CategoryIngredient::class,
            'compound' => true,
            'attr' => [ 
                'class' => 'catList'
             ], 
            //permet d'ajouter le filtrage 
            // 'class' => 'App\Entity\CategoryIngredient',
            'mapped' => false,  
            'choice_label' => 'name'
        ])
        
        // $formModifier = function (FormInterface $form, CategoryIngredient $categoryIngredient = null) {
        //     $ingredients = null === $categoryIngredient ? [] : $categoryIngredient->getIngredients();
            
            // ->add('ingredient', EntityType::class, [
            //     'class' => Ingredient::class,
            //     // 'attr' => [
            //     //     'class' => 'IngredientList'
            //     // ],
            //     'placeholder' => '',    
            //     'choices' => $ingredients,
            //     'choice_label' => 'name',
            //     // 'mapped' => false
            // ])
        // };

        // $builder->addEventListener(
        //     //PRE_SET : pour modifier les données de pré-envoie
        //     //garde synchronisation du form avec donnée choisir
        //     //FormEvent donne un procédé pour modifier son form dynamiquement
        //     // FormEvents::PRE_SET_DATA,
        //     FormEvents::PRE_SET_DATA,
        //     function (FormEvent $event) use ($formModifier) {
        //         //deviendra l'entité i.e composition
        //         $data = $event->getData();
        //         if($data)
        //         //prend le formulaire avec les données filtrées
        //         $formModifier($event->getForm(), $data->getCategory());
        //     }
        // );

        // $builder->get('categoryIngredient')->addEventListener(
        //     //soumet le champ modifié
        //     FormEvents::POST_SUBMIT,
        //     function (FormEvent $event) use ($formModifier) {
        //         // Important de fetch avec  $event->getForm()->getData() 
        //         // $event->getData() me permettra d'avoir la bonne ID 
        //         $categoryIngredient = $event->getForm()->getData();

        //         $formModifier($event->getForm()->getParent(), $categoryIngredient);
        //     }
        // )
        
        ->add('ingredient', EntityType::class, [
            'class' => Ingredient::class,
            'attr' => [
                "class" => "ingrList",
                "style" => "display:none;"
            ],
            'choice_label' => 'name',
            'label' => false
        ])
        ->add("quantity")
        ->add("unit")
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Composition::class,
        ]);
    }
}
