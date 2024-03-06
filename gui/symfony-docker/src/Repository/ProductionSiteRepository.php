<?php

namespace App\Repository;

use App\Entity\ProductionSite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductionSite>
 *
 * @method ProductionSite|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductionSite|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductionSite[]    findAll()
 * @method ProductionSite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductionSiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductionSite::class);
    }

//    /**
//     * @return ProductionSite[] Returns an array of ProductionSite objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ProductionSite
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
