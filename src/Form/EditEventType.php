<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;


class EditEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank()
                ],
                'label' => 'Titre de l\'événement',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'constraints' => [
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'The date must be today or later.'
                    ])
                    ],
                'attr' => ['class' => 'form-control']

            ])
            ->add('heure', TextType::class, [
                'label' => 'Heure de l\'événement',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('nbrMaxParticipants', IntegerType::class, [
                'label' => 'Nombre de participants maximum',
                'attr' => ['class' => 'form-control']
            ])
            ->add('publique', CheckboxType::class, [
                'required' => false,
                'label' => 'L\'événement est-il publique ?',
                'attr' => ['class' => 'form-check form-switch']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'csrf_protection' => true,
        ]);
    }
}