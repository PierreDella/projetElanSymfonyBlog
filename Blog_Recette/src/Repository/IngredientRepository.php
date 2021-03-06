<?php

namespace App\Repository;

use App\Entity\Ingredient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ingredient|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ingredient|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ingredient[]    findAll()
 * @method Ingredient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }
    
     /**
     * @return Ingredient[] Returns an array of Ingredient objects
      */
    public function findByCategorie($cat)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.category = :val')
            ->setParameter('val', $cat)
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function getAll(){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT i
                FROM App\Entity\Ingredient i
                ORDER  BY i.category    '
        );
        return $query->execute();
    }

    /*
    public function findOneBySomeField($value): ?Ingredient
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
