<?php

namespace App\Handlers;

use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EleveurHandler
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function isAllowedToTouch(?Resource $resource, UserInterface $user) : bool
    {
        return $resource
            && $resource->getResourceName()->getResourceCategory()->getCategory() == 'ANIMAL'
            && $resource->getCurrentOwner()->getWalletAddress() == $user->getWalletAddress();
    }

    public function vaccineAnimal(String $vaccine, Resource $resource) : void
    {
        $date = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $dateString = $date->format('Y-m-d');
        $resource->setDescription($resource->getDescription() . 'VACCIN|' . $vaccine . '|' . $dateString . ';');
        $this->entityManager->persist($resource);
        $this->entityManager->flush();

    }

    public function addNutrition(String $nutrition, Resource $resource) : void
    {
        $resource->setDescription($resource->getDescription() . 'NUTRITION|' . $nutrition . ';');
        $this->entityManager->persist($resource);
        $this->entityManager->flush();
    }

    public function addDisease(String $disease, String $beginDate, Resource $resource) : void
    {
        $resource->setDescription($resource->getDescription() .
            'MALADIE|' . $disease . '|' . $beginDate . ';');

        $this->entityManager->persist($resource);
        $this->entityManager->flush();
    }
}
