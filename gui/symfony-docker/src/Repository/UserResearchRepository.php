<?php

namespace App\Repository;

use App\Entity\UserResearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserResearch>
 *
 * @method UserResearch|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserResearch|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserResearch[]    findAll()
 * @method UserResearch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserResearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserResearch::class);
    }

    //    /**
    //     * @return UserResearch[] Returns an array of UserResearch objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?UserResearch
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findUserResearchByUserId(int $id): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.User = ' . $id)
            ->getQuery()
            ->getResult()
        ;
    }
}
