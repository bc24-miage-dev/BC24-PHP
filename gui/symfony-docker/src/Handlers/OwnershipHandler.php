<?php

namespace App\Handlers;

use App\Entity\OwnershipAcquisitionRequest;
use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OwnershipHandler
{
    public function ownershipRequestCreate(UserInterface $requester,
                                           EntityManagerInterface $entityManager,
                                           Resource $resource): void
    {
        $request = new OwnershipAcquisitionRequest();
        $request->setRequestDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $request->setRequester($requester);
        $request->setInitialOwner($resource->getCurrentOwner());
        $request->setResource($resource);
        $request->setState('En attente');
        $entityManager->persist($request);
        $entityManager->flush();
    }
}
