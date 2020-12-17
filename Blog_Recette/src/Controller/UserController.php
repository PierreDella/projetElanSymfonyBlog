<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Recipe;
use App\Form\UserType;
use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="user_index")
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $users = $repo->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }


    /** 
     * @Route("/subscribe/{id}", name="add_following")
     */
    public function makeSubscription(User $target, EntityManagerInterface $em){
        
        $subscription = new Subscription();

        $subscription->setSubscriber($this->getUser());
        $subscription->setTargetUser($target);

        $em->persist($subscription);
        $em->flush();

        return $this->redirectToRoute("user_show", ["id"=>$this->getUser()->getId()]);
    }   

    /**
     * @Route("/user/{id}", name="user_show", methods="GET")
     */
    public function show(User $user = null, EntityManagerInterface $manager)
    {   
        $subscribers = $this->getUser()->getSubscribers();

        $subscribers = $user->getSubscribers();
        $userSubscribers = [];
        foreach($subscribers as $subscriber) {
            $userSubscribers[] = [
                "pseudo" => $user->getPseudo()
            ];
        }

        $subscriptions = $this->getUser()->getListsubscriptions();

        $others = $manager->getRepository(User::class)->findAll();

        
        //Ce fait grace au @ParamConverter

        // On veut recuperer la liste des user et la donner a twig
        // On veut un repository dans lequel on interragi avec doctrine, où l'on veut un repository qui gere user
        //On rajoute App/Entity/Repository
        // $repository = $this->getDoctrine()->getRepository(User::class);
        // Ensuite on lui dit sous quelle forme on veut les infos
        // $user = $repository->find($id);

        //Cpdt les dependances permettent de ce contenter de ca avec @ParamConverter

        if ($user) {
            return $this->render('user/show.html.twig', [
                "userSubscribers" => $userSubscribers,
                'user' => $user,
                "subscriptions" => $subscriptions,
                "subscribers" => $subscribers,
                "others" => $others
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/user/delete/{id}", name="user_delete")
     */
    public function deleteUser(User $user, EntityManagerInterface $manager)
    {
        $manager->remove($user);
        $manager->flush();

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/user/{id}/edit", name="user_edit")
     */
    public function editUser(User $user = null, Request $request, EntityManagerInterface $manager): Response
    {

        if ($user) {
            if ($user->getId() == $this->getUser()->getId()) {
                $form = $this->createForm(UserType::class, $user);

                $form->handleRequest($request);


                if ($form->isSubmitted() && $form->isValid()) {

                    dd($form['picture']->getData());
                    $manager->persist($user);
                    $manager->flush();
                    return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
                }

                return $this->render("user/edit.html.twig", [
                    "formUser" => $form->createView(),
                    "user" => $user

                ]);
            } else {

                return $this->redirectToRoute('home');
            }
        } else {
            return $this->redirectToRoute('home');
        }
    }

    
}
