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

class EleveurVaccineType extends AbstractType
{
    private BlockChainService $blockChainService;
    public function __construct(BlockChainService $blockChainService)
    {
        $this->blockChainService = $blockChainService;
    }
    public function configureOptions(OptionsResolver $resolver)
{
    $resolver->setDefaults([
        // other defaults...
        'id' => null, // Define the default value or requirement for 'id'
        'Vaccin' => 0,
    ]);
}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('Vaccin', TextType::class, [
            'data' => $options["Vaccin"],
        ])
        ->add('Vacciner', SubmitType::class, [])
        ;
    }
}

