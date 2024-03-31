<?php

namespace App\Handlers;

use App\Entity\UserResearch;
use App\Entity\User;
use App\Entity\Resource;

class UserResearchHandler
{
    public function createUserResearch(User $user, Resource $resource): UserResearch
    {
        $userResearch = new UserResearch();
        $userResearch->setUser($user);
        $userResearch->setResource($resource);
        $userResearch->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        return $userResearch;
    }
}