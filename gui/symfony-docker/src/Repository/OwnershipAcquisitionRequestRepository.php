<?php

namespace App\Repository;

use App\Entity\OwnershipAcquisitionRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OwnershipAcquisitionRequest>
 *
 * @method OwnershipAcquisitionRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method OwnershipAcquisitionRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method OwnershipAcquisitionRequest[]    findAll()
 * @method OwnershipAcquisitionRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OwnershipAcquisitionRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OwnershipAcquisitionRequest::class);
    }

//    /**
//     * @return OwnershipAcquisitionRequest[] Returns an array of OwnershipAcquisitionRequest objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OwnershipAcquisitionRequest
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
