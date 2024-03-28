<?php

namespace App\Handlers;

use App\Entity\Resource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class proAcquireHandler
{
    public static function acquireStrict($form, $doctrine, $user, $ResourceCategoryCondition) : bool
    {
        $data = $form->getData();
        $id = $data->getId();

        $resource = $doctrine->getRepository(Resource::class)->find($id);
        if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != $ResourceCategoryCondition) {
            return(false);
        }
        $resource->setCurrentOwner($user);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($resource);
        $entityManager->flush();
        return(true);
    }

    public static function acquire($form, $doctrine, $user) : bool
    {
        $data = $form->getData();
        $id = $data->getId();

        $resource = $doctrine->getRepository(Resource::class)->find($id);
        if (!$resource) {
            return(false);
        }
        $resource->setCurrentOwner($user);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($resource);
        $entityManager->flush();
        return(true);
    }
}
