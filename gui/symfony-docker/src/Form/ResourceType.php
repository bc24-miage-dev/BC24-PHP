<?php

namespace App\Form;

use App\Entity\ProductionSite;
use App\Entity\Resource;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ResourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id')
            ->add('ResourceName')
            ->add('isFinalProduct')
            ->add('isContamined')
            ->add('weight')
            ->add('price')
            ->add('description')
            ->add('components', EntityType::class, [
                'class' => Resource::class,
                'choice_label' => 'id',
                'required' => false,
                'multiple' => true,
            ])
            ->add('origin', EntityType::class, [
                'class' => ProductionSite::class,
'choice_label' => 'ProductionSiteName',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Add Resource',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Resource::class,
        ]);
    }
}
