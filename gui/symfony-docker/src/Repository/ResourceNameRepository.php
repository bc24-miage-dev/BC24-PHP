<?php

namespace App\Repository;

use App\Entity\ResourceName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceName>
 *
 * @method ResourceName|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceName|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceName[]    findAll()
 * @method ResourceName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResourceName::class);
    }

    //    /**
    //     * @return ResourceName[] Returns an array of ResourceName objects
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

    //    public function findOneBySomeField($value): ?ResourceName
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByCategoryAndFamily(String $category, String $family): array
    {
        return $this->createQueryBuilder('rn')
            ->join('rn.resourceCategory', 'rc')
            ->join('rn.family', 'rf')
            ->andWhere('rc.category = :category')
            ->andWhere('rf.name = :family')
            ->setParameter('category', $category)
            ->setParameter('family', $family)
            ->getQuery()
            ->getResult();
    }
}
