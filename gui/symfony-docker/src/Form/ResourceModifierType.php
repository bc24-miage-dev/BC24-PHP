<?php

namespace App\Form;

use App\Entity\ProductionSite;
use App\Entity\Resource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ResourceModifierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ResourceName')
            ->add('isFinalProduct')
            ->add('isContamined')
            ->add('weight')
            ->add('price')
            ->add('description')
            ->add('origin', EntityType::class, [
                'class' => ProductionSite::class,
'choice_label' => 'id',
            ])
            ->add('components', EntityType::class, [
                'class' => Resource::class,
'choice_label' => 'id',
'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
            ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Resource::class,
        ]);
    }
}
