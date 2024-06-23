<?php

namespace App\Handlers;

use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Service\BlockChainService;
use App\Repository\UserRepository;
use App\Entity\OwnershipAcquisitionRequest;

class TransactionHandler
{
    private OwnershipAcquisitionRequestRepository $requestRepository;
    private EntityManagerInterface $entityManager;
    private OwnershipHandler $ownershipHandler;
    private ResourceRepository $resourceRepo;
    private BlockChainService $blockChainService;

    public function __construct(EntityManagerInterface $entityManager,
                                OwnershipAcquisitionRequestRepository $requestRepository,
                                OwnershipHandler $ownershipHandler,
                                ResourceRepository $resourceRepo,
                                BlockChainService $blockChainService)
    {
        $this->requestRepository = $requestRepository;
        $this->entityManager = $entityManager;
        $this->ownershipHandler = $ownershipHandler;
        $this->resourceRepo = $resourceRepo;
        $this->blockChainService = $blockChainService;
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
        $quantity = 0;
        $ListeResource = $this->blockChainService->getResourceWalletAddress($request->getInitialOwner()->getWalletAddress());
        foreach ($ListeResource as $key => $resource) {
            if($resource['tokenId'] == $request->getResourceTokenID()){
                $quantity = $resource['balance'];
            }
        }
        $this->blockChainService->transferResource($request->getResourceTokenID(),$quantity,$request->getInitialOwner()->getWalletAddress(), $request->getRequester()->getWalletAddress());
        $request->setState('Validé');
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
    public function askOwnership( UserInterface $userID, UserInterface $receiverID, int $tokenID ) : void
    {
        $listResources = $this->blockChainService->getAllRessourceFromWalletAddress($userID->getWalletAddress());
        foreach ($listResources as $key => $value) {
            if ($tokenID ==  $value['tokenId']) {
                $request = new OwnershipAcquisitionRequest();
                $request->setRequestDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
                $request->setRequester($receiverID);
                $request->setInitialOwner($userID);
                $request->setResourceTokenID($tokenID);
                $request->setState('En attente');
                $this->entityManager->persist($request);
                $this->entityManager->flush();
                return;
            }
        }
    
        if ($this->requestRepository->findOneBy(['requester' => $userID, 'resourceTokenID' => $tokenID, 'state' => 'En attente'])){
            throw new Exception('Vous avez déjà demandé la propriété de cette ressource');
        }

        throw new Exception('Vous ne pouvez pas envoyer cette ressource');
    }
}
