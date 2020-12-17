<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/followings", name="my_followings")
     */
    public function SubIndex(EntityManagerInterface $manager) {
       
        $usersIFollow = $this->getUser()->getListSubscriptions();
        $usersWhoFollowMe = $this->getUser()->getSubscribers();

        $others = $manager->getRepository(User::class)->findAll();

        return $this->render('user/show.html.twig', [
            "listSubscriptions" => $usersIFollow,
            "subscribers" => $usersWhoFollowMe,
            "others" => $others
        ]);
    }

    /** 
     * @Route("/follow/{id}", name="add_following")
     */
    public function followSomeone(User $user, EntityManagerInterface $manager){
       
        $subscription = new Subscription();

        $subscription->setSubscriber($this->getUser());
        $subscription->setTargetUser($user);

        $manager->persist($subscription);
        $manager->flush();

        return $this->redirectToRoute("my_followings");
    }
}
