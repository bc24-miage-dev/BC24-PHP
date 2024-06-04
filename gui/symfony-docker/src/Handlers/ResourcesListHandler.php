<?php

namespace App\Handlers;

use App\Repository\ResourceRepository;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ResourcesListHandler
{

    private ResourceRepository $resourceRepo;

    public function __construct(ResourceRepository $resourceRepo)
    {
        $this->resourceRepo = $resourceRepo;
    }


    public function getResources(UserInterface $user,
                                 ?String $category = null) : array
    {
        // If no POST method is used, return all resources
        if ($category) { // If a category is specified, return all resources of that category
            $resources = $this->resourceRepo->findByWalletAddressCategory($user->getWalletAddress(), $category);
        } else {
            $resources = $this->resourceRepo->findByWalletAddress($user->getWalletAddress());
        }
        return $resources;
    }


    /**
     * @throws Exception
     */
    public function getSpecificResource(String $NFC,
                                        UserInterface $user) : array
    {
        $resourceSpecific = $this->resourceRepo->findByWalletAddressAndNFC($user->getWalletAddress(), $NFC);
        if(!$resourceSpecific){ // If the resource does not exist
            throw new Exception('Cette ressource ne vous appartient pas');
        }
        return $resourceSpecific; // Return the specific resource found (in an array, for consistency)
    }
}
