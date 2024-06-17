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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EleveurDiseaseType extends AbstractType
{
    private BlockChainService $blockChainService;

    public function __construct(BlockChainService $blockChainService)
    {
        $this->blockChainService = $blockChainService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $isContaminated = $options['isContaminated'];
    $builder
        ->add('isContaminated', ChoiceType::class, [
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
            'data' => $isContaminated,
        ])
        ->add("Informer", SubmitType::class, [])
    ;
}
        public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // other defaults...
            'id' => null, // Define the default value or requirement for 'id'
            'isContaminated' => false,
        ]);
    }
}
