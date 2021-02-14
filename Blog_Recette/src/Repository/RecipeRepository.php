<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Recipe;
use App\Data\SearchData;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
        
            ->createQueryBuilder('r') // recupere recipes
            //permettra avec join de recupérer les infos en une seule requete
            
            ->select('c', 'r')
            ->join('r.categories', 'c') //recuperera toutes les infos en une seule requete
            ->andWhere('r.published=1');
            // ->orderBy('nblikes', 'DESC');
            // ->orderBy('')
            

        if( ! empty($search->q)) { //recupere requete q si pas vide
            $query = $query 
                ->andWhere('r.name LIKE :q') //on veut que le nom de la recette soit le même que le parametre q
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
    
    /**
     * @return boolean
     */
    public function isLikedByUser(User $user) : bool {

        foreach($this->likes as $like){ 
                // si dans les likes se trouve l'utilisateur ca veut dire qu'il aura liké
            if($like->getUser() === $user) return true;
        
        }

        return false;
    }
    public function getAllOrder() {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT r
                FROM App\Entity\Recipe r
                ORDER BY r.id DESC'
        )
        ->setMaxResults(15);

        return $query->execute();
    }

    // public function getAllOrderRecipe() {
    //     $entityManager = $this->getEntityManager();
    //     $query = $entityManager->createQuery(
    //         'SELECT r.name, r.created_at
    //         FROM App\Entity\Recipe r, App\Entity\User u
    //         WHERE u.id = r.user_id
    //         AND u.id = :id
    //         ORDER BY r.created_at DESC'
    //     )
    //     ->setMaxResults(3);

    //     return $query->execute();
    // }
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

    /**
    * @return Recipe[] Returns an array of Recipe objects
    */
    public function findRecipesByUserSubscription($user)
    {
        return $this->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->join('u.subscribers', 's')
            ->where('s.subscriber = :me')
            ->setParameter('me', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

}
