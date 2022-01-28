<?php

namespace App\Repository;


use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findPage(int $page = 1, int $limit = 18): array
    {
        return $this->findBy([], [], $limit, $limit * ($page - 1));
    }

    public function findWinesBySkus(array $skus)
    {
        $products = $this->createQueryBuilder('p')
            ->andWhere('p.sku IN (:skus)')
            ->setParameter('skus', $skus)
            ->getQuery()
            ->getResult();

        return $products;
    }

    public function findBySkuAndPrice(string $sku, float $minPrice, float $maxPrice, float $specialPrice)
    {
        $products = $this->createQueryBuilder('p')
            ->andWhere('p.sku = :sku')
            ->andWhere('p.specialPrice = :specialPrice')
            ->andWhere('p.price >= :minPrice')
            ->andWhere('p.price <= :maxPrice')
            ->setParameter('sku', $sku)
            ->setParameter('maxPrice', $maxPrice)
            ->setParameter('minPrice', $minPrice)
            ->setParameter('specialPrice', $specialPrice)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
        if (count($products) > 0)
        {
            return $products[0];
        }

        return null;
    }


    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}