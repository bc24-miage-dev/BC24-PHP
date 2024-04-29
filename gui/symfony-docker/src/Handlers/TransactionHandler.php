<?php

namespace App\Handlers;

use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

class TransactionHandler
{
    private OwnershipAcquisitionRequestRepository $requestRepository;
    private EntityManagerInterface $entityManager;
    private OwnershipHandler $ownershipHandler;
    private ResourceRepository $resourceRepo;

    public function __construct(EntityManagerInterface $entityManager,
                                OwnershipAcquisitionRequestRepository $requestRepository,
                                OwnershipHandler $ownershipHandler,
                                ResourceRepository $resourceRepo)
    {
        $this->requestRepository = $requestRepository;
        $this->entityManager = $entityManager;
        $this->ownershipHandler = $ownershipHandler;
        $this->resourceRepo = $resourceRepo;
    }

    /**
     * @throws Exception
     */
    public function acceptTransaction(int $id, UserInterface $user) : void
    {
        $request = $this->requestRepository->find($id);
        if (!$request || $request->getInitialOwner() != $user || $request->getState() != 'En attente') {
            throw new Exception('Erreur lors de la transaction');
        }
        $resource = $request->getResource();
        $resource->setCurrentOwner($request->getRequester());
        $request->setState('Validé');
        $this->entityManager->persist($resource);
        $this->entityManager->persist($request);
        $this->entityManager->flush();
    }

    /**
     * @throws Exception
     */
    public function refuseTransaction(int $id, UserInterface $user) : void
    {
        $request = $this->requestRepository->find($id);
        if (!$request || $request->getInitialOwner() != $user || $request->getState() != 'En attente'){
            throw new Exception('Erreur lors de la transaction');
        }
        $request->setState('Refusé');
        $this->entityManager->persist($request);
        $this->entityManager->flush();
    }

    /**
     * @throws Exception
     */
    public function acceptAllTransactions(UserInterface $user) : void
    {
        $requests = $this->requestRepository->findBy(['initialOwner' => $user, 'state' => 'En attente']);
        if (!$requests){
            throw new Exception('Il n\'y a pas de transaction à effectuer');
        }
        foreach ($requests as $request){
            $resource = $request->getResource();
            $resource->setCurrentOwner($request->getRequester());
            $request->setState('Validé');
            $this->entityManager->persist($resource);
            $this->entityManager->persist($request);
        }
        $this->entityManager->flush();
    }

    /**
     * @throws Exception
     */
    public function askOwnership(int $id, UserInterface $user) : void
    {
        $resource = $this->resourceRepo->find($id);
        if (!$resource || $resource->getCurrentOwner()->getWalletAddress() == $user->getWalletAddress() || $resource->isIsLifeCycleOver()){
            throw new Exception('Vous ne pouvez pas demander la propriété de cette ressource');

        }
        if ($this->requestRepository->findOneBy(['requester' => $user, 'resource' => $resource, 'state' => 'En attente'])){
            throw new Exception('Vous avez déjà demandé la propriété de cette ressource');

        }
        $this->ownershipHandler->ownershipRequestCreate($user, $this->entityManager, $resource);
    }
}
