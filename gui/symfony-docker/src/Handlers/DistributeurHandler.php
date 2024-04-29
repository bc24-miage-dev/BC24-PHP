<?php

namespace App\Handlers;

use App\Entity\Resource;
use App\Repository\ResourceRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DistributeurHandler extends ProHandler
{

    /**
     * @throws Exception
     */
    public function saleProcess(int $nfc, UserInterface $user) : void
    {
        $resource = $this->resourceRepository->findOneBy(['id' => $nfc]);

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
        return $this->resourceRepository->findBy(['currentOwner' => $user, 'IsLifeCycleOver' => true],
            ['date' => 'DESC'],
            limit: 30);
    }
}
