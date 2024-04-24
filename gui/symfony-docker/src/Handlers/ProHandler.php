<?php

namespace App\Handlers;

use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProHandler
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function canHaveAccess(?Resource $resource, UserInterface $user) : bool
    {
        return $resource && $resource->getCurrentOwner()->getWalletAddress() == $user->getWalletAddress() && !$resource->isIsLifeCycleOver();
    }
}
