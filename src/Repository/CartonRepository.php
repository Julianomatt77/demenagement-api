<?php

namespace App\Repository;

use App\Entity\Carton;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Carton>
 */
class CartonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carton::class);
    }

    public function findByUserGroupedByRoom($user)
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.room', 'r')
            ->where('c.user = :user')
            ->andWhere('c.deleted_at IS NULL')
            ->orderBy('r.name', 'ASC')
            ->addOrderBy('c.numero', 'ASC')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Carton[] Returns an array of Carton objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Carton
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
