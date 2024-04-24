<?php

namespace App\Handlers;

use App\Entity\UserRoleRequest;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRoleRequestHandler
{

    private EntityManagerInterface $entityManager;

     public function __construct(EntityManagerInterface $entityManager)
     {
            $this->entityManager = $entityManager;
     }

     public function initializeRoleRequest(UserRoleRequest $userRoleRequest, UserInterface $user): bool
     {
         $userRoleRequest->setUser($user);
         $userRoleRequest->setRead(false);
         $userRoleRequest->setDateRoleRequest(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
         $this->entityManager->persist($userRoleRequest);
         $this->entityManager->flush();
         return true;
     }
}
