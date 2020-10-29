<?php

namespace App\Repository;

use App\Entity\CategoryRecipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryRecipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryRecipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryRecipe[]    findAll()
 * @method CategoryRecipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryRecipe::class);
    }

    // /**
    //  * @return CategoryRecipe[] Returns an array of CategoryRecipe objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CategoryRecipe
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
