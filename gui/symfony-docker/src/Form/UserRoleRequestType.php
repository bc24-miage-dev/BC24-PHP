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
                    'Éleveur' => 'ROLE_ELEVEUR',
                    'Transporteur' => 'ROLE_TRANSPORTEUR',
                    'Équarrisseur' => 'ROLE_EQUARRISSEUR',
                    'Usine' => 'ROLE_USINE',
                    'Commerçant' => 'ROLE_COMMERCANT',
                    'Admin' => 'ROLE_ADMIN',
                ],])
                ->add('Description', null, ['label' => 'Description'])
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
