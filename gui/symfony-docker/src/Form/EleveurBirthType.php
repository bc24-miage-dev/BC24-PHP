<?php

namespace App\Form;

use App\Entity\ProductionSite;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Service\BlockChainService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class EleveurBirthType extends AbstractType
{
    public function __construct(BlockChainService $blockChainService)
    {
        $this->blockChainService = $blockChainService;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $resourceTemplates = $this->blockChainService->getResourceIDFromRole("BREEDER");

        $builder
        ->add('resourceName', ChoiceType::class, [
            'choices' => $resourceTemplates,
            'choice_label' => function ($choice, $key, $value) {
                return $key; //i swear i don t know why it works
            },
            'choice_value' => function ($choice) {
                return $choice;
            },
        ])
            ->add('price')
            ->add('Genre')
            ->add('weight', IntegerType::class)
            ->add('description', TextareaType::class) // Set description as TextareaType
            ->add('submit', SubmitType::class, [
                'label' => 'Confirmer la naissance'
            ])

        ;
    }

    // public function configureOptions(OptionsResolver $resolver): void
    // {
    //     $resolver->setDefaults([
    //         'data_class' => Resource::class,
    //     ]);
    // }
}
