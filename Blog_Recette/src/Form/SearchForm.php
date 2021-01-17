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
        return ''; //Par default mettra la recherche dans search::data
        //Permet d'avoir un URL la plus propre possible
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder //creation du formulaire
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
            //qulle classe sert pour représenter nos données
            'data_class' =>SearchData::class,
            //methode get par default car les données passeront par URL pour pouvoir partager une recherche
            'method' => 'GET',
            //desactivation de la protection CSRF
            'csrf_protection' => false
        ]);
        // $resolver->setDefaults([
        //     'data_class' => Recipe::class,
        // ]);
    }
}
