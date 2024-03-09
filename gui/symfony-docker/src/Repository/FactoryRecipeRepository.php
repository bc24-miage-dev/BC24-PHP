<?php

namespace App\Repository;

use App\Entity\FactoryRecipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FactoryRecipe>
 *
 * @method FactoryRecipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method FactoryRecipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method FactoryRecipe[]    findAll()
 * @method FactoryRecipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactoryRecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactoryRecipe::class);
    }

    //    /**
    //     * @return FactoryRecipe[] Returns an array of FactoryRecipe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FactoryRecipe
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
