<?php

namespace App\Handlers;

use App\Entity\Resource;
use App\Repository\ResourceRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DistributeurHandler extends ProHandler
{
    private ResourceRepository $resourceRepo;
    public function __construct(EntityManagerInterface $entityManager, ResourceRepository $resourceRepo)
    {
        parent::__construct($entityManager);
        $this->resourceRepo = $resourceRepo;
    }

    public function canHaveAccess(?Resource $resource, UserInterface $user) : bool
    {
        return parent::canHaveAccess($resource, $user)
            && $resource->getResourceName()->getResourceCategory()->getCategory() == 'PRODUIT';
    }

    /**
     * @throws Exception
     */
    public function saleProcess(int $nfc, UserInterface $user) : void
    {
        $resource = $this->resourceRepo->findOneBy(['id' => $nfc]);

        if (!$this->canHaveAccess($resource, $user))
        {
            throw new Exception('Aucun produit vous appartenant avec ce tag NFC n\'a été trouvé');
        }

        $resource->setIsLifeCycleOver(true);
        $this->entityManager->persist($resource);
        $this->entityManager->flush();
    }

    public function getRecentSalesHistory(UserInterface $user) : array
    {
        return $this->resourceRepo->findBy(['currentOwner' => $user, 'IsLifeCycleOver' => true],
            ['date' => 'DESC'],
            limit: 30);
    }
}
