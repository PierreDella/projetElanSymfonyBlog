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
            'attr' => [ 
                'class' => 'catList'
             ], 
            'mapped' => false,  
            'choice_label' => 'name'
        ])
        
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
