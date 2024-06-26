<?php

namespace App\Repository;

use App\Entity\Resource;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Resource>
 *
 * @method resource|null find($id, $lockMode = null, $lockVersion = null)
 * @method resource|null findOneBy(array $criteria, array $orderBy = null)
 * @method resource[]    findAll()
 * @method resource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resource::class);
    }

//    /**
//     * @return Resource[] Returns an array of Resource objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Resource
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findByWalletAddress(String $walletAddress): array
    {
        return $this->createQueryBuilder('r')
                ->join('r.currentOwner', 'c')
            ->andWhere('r.IsLifeCycleOver = false')
            ->andWhere('c.WalletAddress = :WalletAddress')
            ->setParameter('WalletAddress', $walletAddress)
            ->getQuery()
            ->getResult();
    }

    public function findByWalletAddressAndNFC(String $walletAddress, int $NFC): array
    {
        return $this->createQueryBuilder('r')
                ->join('r.currentOwner', 'c')
            ->andWhere('r.IsLifeCycleOver = false')
            ->andWhere('c.WalletAddress = :WalletAddress')
            ->andWhere('r.id = :NFC')
            ->setParameter('WalletAddress', $walletAddress)
            ->setParameter('NFC', $NFC)
            ->getQuery()
            ->getResult();
    }

    public function findByWalletAddressCategory(String $walletAddress, String $category): array
    {
        $category = strtoupper($category);

        return $this->createQueryBuilder('r')
                ->join('r.currentOwner', 'u')
                ->join('r.ResourceName', 'rn')
                ->join('rn.resourceCategory', 'rc')
            ->andWhere('r.IsLifeCycleOver = false')
            ->andWhere('rc.category = :category')
            ->andWhere('u.WalletAddress = :WalletAddress')
            ->setParameter('category', $category)
            ->setParameter('WalletAddress', $walletAddress)
            ->getQuery()
            ->getResult();
    }




    public function findByWalletAddressNFC(String $walletAddress, int $NFC): array
    {
        return $this->createQueryBuilder('r')
                ->join('r.currentOwner', 'u')
                ->join('r.ResourceName', 'rn')
                ->join('rn.resourceCategory', 'rc')
            ->andWhere('r.IsLifeCycleOver = false')
            ->andWhere('u.WalletAddress = :WalletAddress')
            ->andWhere('r.id = :NFC')
            ->setParameter('WalletAddress', $walletAddress)
            ->setParameter('NFC', $NFC)
            ->getQuery()
            ->getResult();
    }

    public function findProductByWalletAddress(String $walletAddress): array
    {
        return $this->createQueryBuilder('r')
                ->join('r.currentOwner', 'u')
                ->join('r.ResourceName', 'rn')
                ->join('rn.resourceCategory', 'rc')
            ->andWhere('r.IsLifeCycleOver = false')
            ->andWhere('rn.productionSiteOwner != :productionSiteOwner')
            ->andWhere('u.WalletAddress = :WalletAddress')
            ->setParameter('WalletAddress', $walletAddress)
            ->setParameter('productionSiteOwner', false)
            ->getQuery()
            ->getResult();
    }

    public function getFewLastResources(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.date', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }
}
