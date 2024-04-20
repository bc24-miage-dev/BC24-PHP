<?php

namespace App\Handlers;

use App\Entity\UserResearch;
use App\Entity\Resource;
use App\Repository\UserResearchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Security\Core\User\UserInterface;

class UserResearchHandler
{
    private EntityManagerInterface $entityManager;
    private UserResearchRepository $userResearchRepository;
    public function __construct(EntityManagerInterface $entityManager, UserResearchRepository $userResearchRepository)
    {
        $this->entityManager = $entityManager;
        $this->userResearchRepository = $userResearchRepository;
    }


    private function createUserResearch(UserInterface $user, Resource $resource): UserResearch
    {
        $userResearch = new UserResearch();
        $userResearch->setUser($user);
        $userResearch->setResource($resource);
        $userResearch->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        return $userResearch;
    }

    public function userResearchRecordingProcess(?UserInterface $user, Resource $resource) : bool
    {
        if ($user) { // User connecté
            $history = $this->userResearchRepository->findOneBy(['User' => $user, 'Resource' => $resource]);
            if ($history) {
                $history->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                $this->entityManager->persist($history);
            } else {
                $this->entityManager->persist($this->createUserResearch($user, $resource));
            }
            $this->entityManager->flush();
            return true;
        }
        return false;
    }
}
