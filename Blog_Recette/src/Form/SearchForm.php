<?php

namespace App\Form;

use FFI\CData;
use App\Entity\Recipe;
use App\Data\SearchData;
use App\Entity\CategoryRecipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SearchForm extends AbstractType
{   

    public function getBlockPrefix(){
        return '';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', TextType::class, [
                'label' =>false,
                'required' =>false,
                'attr' => [
                    'placeholder' => 'Rechercher'
                ]
            ])
            ->add('categoriesRecipe', EntityType::class, [
                'label' =>false,
                'required' =>false,
                'class' => CategoryRecipe::class,
                'expanded' => true,
                'multiple' => true
            ])
            ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' =>SearchData::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
        // $resolver->setDefaults([
        //     'data_class' => Recipe::class,
        // ]);
    }
}
