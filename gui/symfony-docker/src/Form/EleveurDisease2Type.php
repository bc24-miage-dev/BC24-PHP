<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\ProductionSite;
use App\Entity\Resource;
use App\Entity\ResourceName;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Service\BlockChainService;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EleveurDisease2Type extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
{
    $resolver->setDefaults([
    ]);
}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('Disease2', TextType::class)
        ->add('Ajouter', SubmitType::class, [])
        ;
    }
}

