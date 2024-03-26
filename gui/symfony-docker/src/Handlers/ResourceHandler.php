<?php

namespace App\Handlers;

use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;


class ResourceHandler
{
    public function findAllChildren(Resource $resource): array {
        $array = [$resource];

        foreach ($resource->getResources() as $childResource) {
            array_push($array, ...$childResource->findAllChildren());
        }

        return $array;
    }


    /**
     * @param EntityManagerInterface $entityManager
     * @param Resource $resource
     */
    public function contaminateChildren(EntityManagerInterface $entityManager, Resource $resource): void
    {

        foreach ($this->findAllChildren($resource) as $parentResource) {
            $parentResource->setIsContamined(true);
            $entityManager->persist($parentResource);

        }

        $entityManager->flush();

    }

}
