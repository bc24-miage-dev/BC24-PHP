<?php

namespace App\Form;

use App\Entity\ProductionSite;
use App\Entity\Resource;
use App\Entity\ResourceName;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Service\BlockChainService;


class ResourceOwnerChangerType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options ): void
    {
        $builder
            ->add('id', IntegerType::class)
            ->add('newOwner', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'label' => 'Envoyer à :'
            ])
            ->add('Demander', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // other default options...
        ]);
    }
}
