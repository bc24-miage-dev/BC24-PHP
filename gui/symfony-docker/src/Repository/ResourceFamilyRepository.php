<?php

namespace App\Repository;

use App\Entity\ResourceFamily;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceFamily>
 *
 * @method ResourceFamily|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceFamily|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceFamily[]    findAll()
 * @method ResourceFamily[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceFamilyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResourceFamily::class);
    }

    //    /**
    //     * @return ResourceFamily[] Returns an array of ResourceFamily objects
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

    //    public function findOneBySomeField($value): ?ResourceFamily
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
