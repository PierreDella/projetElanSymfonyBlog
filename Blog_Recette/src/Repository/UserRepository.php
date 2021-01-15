<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // public function getRecipeByLikes(){
    //         $entityManager = $this->getEntityManager();
    //         $query = $entityManager->createQuery(
    //             'SELECT COUNT(user_id) AS nblikes, recipe_id
    //             FROM App\Entity\RecipeLike
    //             GROUP BY recipe_id
    //             ORDER BY nblikes DESC'
    //         );
    //             return $query->execute();
    // }

    // public function findByTopic($user)
    // {
    //     $entityManager = $this->getEntityManager();
    //     $query = $entityManager->createQuery(
    //         'SELECT p
    //         FROM App\Entity\Post p
    //         WHERE p.topic = :topic'
    //         )
    //     ->setParameter('user', $user);

    //     return $query->getResult(); 
    // }
    
    public function getAllOrder() {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT u
                FROM App\Entity\User u
                ORDER BY u.id DESC'
        )
        ->setMaxResults(15);

        return $query->execute();
    }




    // SELECT name, pseudo, COUNT(rl.user_id) AS nblikes, recipe_id
    // FROM recipe_like rl, recipe r, user u
    // WHERE rl.recipe_id = r.id
    // AND r.user_id = u.id
    // GROUP BY rl.recipe_id	 
    // ORDER BY nblikes DESC
    // public function getByMostliked() {
    //     $entityManager = $this->getEntityManager();
    //     $query = $entityManager->createQuery(
    //         'SELECT u
    //             FROM App\Entity\User u
    //             ORDER BY u.id DESC'
    //     )
    //     ->set
    //     return $query->execute();
    // }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    




    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
