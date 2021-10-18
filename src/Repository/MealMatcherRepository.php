<?php

namespace App\Repository;

use App\Entity\MealMatcher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MealMatcher|null find($id, $lockMode = null, $lockVersion = null)
 * @method MealMatcher|null findOneBy(array $criteria, array $orderBy = null)
 * @method MealMatcher[]    findAll()
 * @method MealMatcher[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealMatcherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MealMatcher::class);
    }

    // /**
    //  * @return MealMatcher[] Returns an array of MealMatcher objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MealMatcher
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
