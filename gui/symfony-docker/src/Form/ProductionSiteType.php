<?php

namespace App\Form;

use App\Entity\ProductionSite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductionSiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ProductionSiteName')
            ->add('Address')
            ->add('ProductionSiteTel')
            ->add('submit' , SubmitType::class, ['label' => 'Créer un site de production']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductionSite::class,
        ]);
    }
}
