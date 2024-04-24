<?php

namespace App\Handlers;

use App\Entity\UserRoleRequest;
use App\Repository\UserRoleRequestRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRoleRequestHandler
{

    private EntityManagerInterface $entityManager;
    private UserRoleRequestRepository $userRoleRequestRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                UserRoleRequestRepository $userRoleRequestRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRoleRequestRepository = $userRoleRequestRepository;
    }

    public function initializeRoleRequest(UserRoleRequest $userRoleRequest, UserInterface $user): bool
    {
        $userRoleRequest->setUser($user);
        $userRoleRequest->setRead(false);
        $userRoleRequest->setDateRoleRequest(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $this->entityManager->persist($userRoleRequest);
        $this->entityManager->flush();
        return true;
    }

    /**
     * @throws Exception
     */
    public function deleteUser(?UserInterface $user): void
    {
        if (!($user)) {
            throw new Exception('Utilisateur non trouvé');
        }
        $user->setDeletedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
        $relatedRequests = $this->userRoleRequestRepository->findBy(['User' => $user, 'Read' => false]);
        foreach ($relatedRequests as $request) {
            if (!($request->getProductionSite()->isValidate())) {
                $this->entityManager->remove($request->getProductionSite());
            }
            $this->entityManager->remove($request);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
