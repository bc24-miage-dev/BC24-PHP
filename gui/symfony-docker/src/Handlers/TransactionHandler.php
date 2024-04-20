<?php

namespace App\Handlers;

use App\Repository\OwnershipAcquisitionRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

class TransactionHandler
{
    private OwnershipAcquisitionRequestRepository $requestRepository;
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager, OwnershipAcquisitionRequestRepository $requestRepository)
    {
        $this->requestRepository = $requestRepository;
        $this->entityManager = $entityManager;
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
}
