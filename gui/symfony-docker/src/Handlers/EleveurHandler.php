<?php

namespace App\Handlers;

use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EleveurHandler extends ProHandler
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    public function canHaveAccess(?Resource $resource, UserInterface $user): bool
    {
        return parent::canHaveAccess($resource, $user)
            && $resource->getResourceName()->getResourceCategory()->getCategory() == 'ANIMAL';
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
