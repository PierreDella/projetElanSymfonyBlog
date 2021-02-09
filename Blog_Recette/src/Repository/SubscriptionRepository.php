<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\Subscription;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Subscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subscription[]    findAll()
 * @method Subscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }
    // public function getRecipesAbo(){
    //     $entityManager = $this->getEntityManager();
    //     $query = $entityManager->createQuery(
    //         'SELECT r
    //             FROM  App\Entity\Subscription s, App\Entity\Recipe r, App\Entity\User u 
    //             WHERE r.user = s.targetUser
    //             AND u.id = s.subscriber
    //             AND u.id = :id
    //             GROUP BY r.id
    //             ORDER BY r.createdAt DESC'
    //     );
    //     return $query->execute();
    // }
    
    /*
    public function findOneBySomeField($value): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
