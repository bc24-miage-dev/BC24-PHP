<?php

namespace App\Repository;

use App\Entity\UserRoleRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserRoleRequest>
 *
 * @method UserRoleRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRoleRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRoleRequest[]    findAll()
 * @method UserRoleRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRoleRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRoleRequest::class);
    }

    //    /**
    //     * @return UserRoleRequest[] Returns an array of UserRoleRequest objects
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

    //    public function findOneBySomeField($value): ?UserRoleRequest
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    
}
