<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\UserRoleRequest;
use Doctrine\DBAL\Types\StringType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserRoleRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                        ->add('roleRequest', ChoiceType::class , [
                'label' => 'Role Request',
                'choices' => [
                    'éleveur' => 'ROLE_ELEVEUR',
                    'transporteur' => 'ROLE_TRANSPORTEUR',
                    'équarrisseur' => 'ROLE_EQUARRISSEUR',
                    'usine' => 'ROLE_USINE',
                    'commerçant' => 'ROLE_COMMERCANT',
                    'admin' => 'ROLE_ADMIN',
                ],])
                    ->add('Envoyer', SubmitType::class)
                ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserRoleRequest::class,
        ]);
    }
}
