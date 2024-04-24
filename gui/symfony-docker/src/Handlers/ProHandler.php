<?php

namespace App\Handlers;

use App\Entity\Resource;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProHandler
{
    protected EntityManagerInterface $entityManager;
    protected ResourceRepository $resourceRepository;

    public function __construct(EntityManagerInterface $entityManager, ResourceRepository $resourceRepository)
    {
        $this->entityManager = $entityManager;
        $this->resourceRepository = $resourceRepository;
    }

    public function canHaveAccess(?Resource $resource, UserInterface $user): bool
    {
        return $resource && $resource->getCurrentOwner()->getWalletAddress() == $user->getWalletAddress() && !$resource->isIsLifeCycleOver();
    }
}
