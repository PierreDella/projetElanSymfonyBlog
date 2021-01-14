<?php

namespace App\Form;

use App\Entity\CategoryIngredient;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('catIngredient', EntityType::class,[
            'class' => CategoryIngredient::class,
            'mapped' => false,
            'choice_label' => 'name'
        ])
        ;
        $formModifier = function (FormInterface $form, CategoryIngredient $cat = null) {
            $ingr = null === $cat ? [] : $cat->getIngredients();

            $form->add('ingredient', EntityType::class, [
                'class' => 'App\Entity\Ingredient',
                'placeholder' => '',
                'choices' => $ingr,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getCatIngredient());
            }
        );
        $builder->get('catIngredient')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $cat = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $cat);  // Here line 58 and error show this line
            }
        )
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
