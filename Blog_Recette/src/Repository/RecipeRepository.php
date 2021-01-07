<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * @return Recipe[]
     */
    public function findSearch(SearchData $search): array
    {   
        $query = $this
        
            ->createQueryBuilder('r')
            //permettra avec join de recupérer les infos en une seule requete
            ->select('c', 'r')
            ->join('r.categories', 'c')
            ->andWhere('r.published=1');

        if( ! empty($search->q)) {
            $query = $query 
                ->andWhere('r.name LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }

        if( ! empty($search->categoriesRecipe)){
            $query = $query 
                ->andWhere('c.id IN (:categoriesRecipe)')
                // 
                ->setParameter('categoriesRecipe', $search->categoriesRecipe);
        }

        return $query->getQuery()->getResult();
        // return $this->findBy(["published" => 1]);
    }
    

    // /**
    //  * @return Recipe[] Returns an array of Recipe objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Recipe
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
