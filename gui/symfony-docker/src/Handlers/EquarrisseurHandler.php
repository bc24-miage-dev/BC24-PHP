<?php

namespace App\Handlers;

use App\Entity\Resource;
use App\Repository\ResourceNameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class EquarrisseurHandler extends ProHandler
{
    private ResourceNameRepository $resourceNameRepo;
    private ResourceHandler $resourceHandler;

    public function __construct(EntityManagerInterface $em,
                                ResourceNameRepository $resourceNameRepo,
                                ResourceHandler $resourceHandler)
    {
        parent::__construct($em);
        $this->resourceNameRepo = $resourceNameRepo;
        $this->resourceHandler = $resourceHandler;
    }


    public function slaughteringProcess(Resource $animal, Resource $carcasse) : void
    {
        $rN = $this->resourceNameRepo->findOneByCategoryAndFamily('CARCASSE', $animal->getResourceName()->getResourceFamilies()[0]->getName());
        // Since the $animal is an animal, we can assume that it has only one family (vache, porc, etc.)
        $carcasse->setResourceName($rN);
        $animal->setIsLifeCycleOver(true);
        $this->entityManager->persist($animal);
        $this->entityManager->persist($carcasse);
        $this->entityManager->flush();
    }

    public function canSlaughter(?Resource $resource, UserInterface $user): bool
    {
        return parent::canHaveAccess($resource, $user) &&
            $resource->getResourceName()->getResourceCategory()->getCategory() == 'ANIMAL';
    }

    public function slicingProcess(Resource $carcasse,
                                   UserInterface $user,
                                   Request $request) : void
    {
        $rN = $this->resourceNameRepo->findOneByCategoryAndFamily('DEMI-CARCASSE',
            $carcasse->getResourceName()->getResourceFamilies()[0]->getName());
        // Same here, since the $carcasse is a carcasse, we can assume that it has only one family (vache, porc, etc.)
        $firstHalf = $this->resourceHandler->createChildResource($carcasse, $user);
        $firstHalf->setResourceName($rN);
        $secondHalf = $this->resourceHandler->createChildResource($carcasse, $user);
        $secondHalf->setResourceName($rN);

        $firstHalf->setId($request->request->get('tag1'));
        $secondHalf->setId($request->request->get('tag2'));
        $firstHalf->setWeight($request->request->get('weight1'));
        $secondHalf->setWeight($request->request->get('weight2'));
        $carcasse->setIsLifeCycleOver(true);

        $this->entityManager->persist($firstHalf);
        $this->entityManager->persist($secondHalf);
        $this->entityManager->flush();
    }

    public function canSlice(?Resource $resource, UserInterface $user): bool
    {
        return parent::canHaveAccess($resource, $user) &&
            $resource->getResourceName()->getResourceCategory()->getCategory() == 'CARCASSE';
    }
}
