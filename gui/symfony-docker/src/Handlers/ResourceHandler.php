<?php

namespace App\Handlers;

use App\Entity\Resource;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;


class ResourceHandler
{
    public function findAllChildren(Resource $resource): array {
        $array = [$resource];

        foreach ($resource->getResources() as $childResource) {
            array_push($array, ...$this->findAllChildren($childResource));
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

    public function createDefaultNewResource($user) :Resource
    {
        $resource = new Resource();
        $resource->setIsContamined(false);
        $resource->setPrice(0);
        $resource->setDescription('');
        $resource->setOrigin($user->getProductionSite());
        $resource->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $resource->setCurrentOwner($user);
        $resource->setIsLifeCycleOver(false);
        return $resource;
    }

    public function createChildResource(Resource $resource, UserInterface $user): Resource
    {
        $newChildResource = new Resource();
        $newChildResource->setCurrentOwner($user);
        $newChildResource->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $newChildResource->setIsLifeCycleOver(false);
        $newChildResource->setIsContamined(false);
        $newChildResource->setPrice(0);
        $newChildResource->setOrigin($user->getProductionSite());
        $newChildResource->setDescription('');
        $newChildResource->addComponent($resource);
        return $newChildResource;
    }

}
