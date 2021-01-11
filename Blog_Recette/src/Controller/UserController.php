<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Recipe;
use App\Form\UserType;
use App\Entity\Bibliotheque;
use App\Entity\Subscription;
use App\Form\BibliothequeType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\ChangePasswordType;


class UserController extends AbstractController
{


//                                                    ******************AFFICHAGES*****************
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
     * @Route("/show/bibliotheque/{id}", name="show_bibliotheque")
     */
    public function showBiblio(Bibliotheque $bibliotheque) {
        
        $bibliotheques = $bibliotheque->getRecipes();
        $userBibliotheques = [];
        foreach ($bibliotheques as $bibliotheque) {
            $userBibliotheques[] = [
                "name" => $bibliotheque->getName(),
                "id" => $bibliotheque->getId(),
               
            ];
        }
        return $this->render('bibliotheque/showBiblio.html.twig', [
            'userBibliotheques' => $userBibliotheques,
            'bibliotheque' => $bibliotheque        
        ]);
    }

     /**
     * @Route("/user/{id}/bibliotheque", name="bibliotheque_index")
     */
    public function indexBiblio(User $user = null){
        
        $bibliotheques = $this->getUser()->getBibliotheques();

        if($user) {
            return $this->render('bibliotheque/bibliotheque.html.twig',[
                'bibliotheques' => $bibliotheques,
                'user' => $user
            ]);
        }
    }
    
    /**
     * @Route("/user/{id}/recipes", name="recipesUser_index")
     */
    public function indexRecipes(User $user = null){
        
        $recipes = $this->getUser()->getRecipes();

        if($user) {
            return $this->render('user/showRecipesUser.html.twig',[
                'recipes' => $recipes,
                'user' => $user
            ]);
        }
    }

    /**
     * @Route("/user/{id}", name="user_show", methods="GET")
     */
    public function show(User $user = null)
    {   
        
        if ($user) {
            return $this->render('user/show.html.twig', [
                'user' => $user,
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/user/{id}/abonnement", name="abonnements_index", methods="GET")
     */
    public function showAbonnement(User $user = null, EntityManagerInterface $manager)
    {   
        $subscribers = $this->getUser()->getSubscribers();

        $subscriptions = $this->getUser()->getListsubscriptions();
        
        $others = $manager->getRepository(User::class)->findAll();
        
        if ($user) {
            return $this->render('bibliotheque/abo.html.twig', [
                'user' => $user,
                "subscriptions" => $subscriptions,
                "subscribers" => $subscribers,
                "others" => $others
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }


//                                                    ******************EDITIONS/AJOUTS*****************

    /**
     * @Route("/{id}/public", name="biblio_lock")
     * 
     */
    public function lock(Bibliotheque $bibliotheque, EntityManagerInterface $manager){

        if($this->getUser()){
            if($this->getUser()->isAdmin()){
                $closeState = $bibliotheque->getPublique() ? false : true;
                $bibliotheque->setPublique($closeState);
                $manager->persist($bibliotheque);
                $manager->flush($bibliotheque);

                return $this->redirectToRoute('bibliotheque_index', ["id"=>$this->getUser()->getId()]);
            }
        }
        return $this->redirectToRoute('bibliotheque_index');
    }

    

    /**
     * @Route("/{id}/public", name="recipe_lock")
     * 
     */
    public function lockRecipe(Recipe $recipe, EntityManagerInterface $manager){

        if($this->getUser()){
            if($this->getUser()->isAdmin()){
                $closeState = $recipe->getPublished() ? false : true;
                $recipe->setPublished($closeState);
                $manager->persist($recipe);
                $manager->flush($recipe);

                return $this->redirectToRoute('recipesUser_index', ["id"=>$this->getUser()->getId()]);
            }
        }
        return $this->redirectToRoute('recipesUser_index');
    }

    /**
     * @Route("/add/bibliotheque/{id}", name="add_bibliotheque")
     * @Route("/edit/bibliotheque/{id}", name="edit_bibliotheque")
     */
    public function createBibliotheque( User $user = null, Bibliotheque $bibliotheque = null, Request $request, ManagerRegistry $manager): Response {
        
            if ( ! $bibliotheque){

                $bibliotheque = new Bibliotheque();
            }
        

        $form = $this->createForm(BibliothequeType::class, $bibliotheque);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()) {

            $em = $manager->getManager();
            $user->addBibliotheque($bibliotheque);
            $bibliotheque->setUser($this->getuser());
            $em->persist($bibliotheque);
            $em->flush();

            return $this->redirectToRoute('bibliotheque_index', ["id"=>$this->getUser()->getId()]);
        }

        return $this->render('bibliotheque/addBibliotheque.html.twig', [
            
            'formBiblio'=> $form->createView(),
            'bibliotheque' => $bibliotheque,
            'user' => $user

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

        return $this->redirectToRoute("abonnements_index", ["id"=>$this->getUser()->getId()]);
    }

    /**
     * @Route("/user/{id}/changePassword", name="user_edit_password")
     */
    public function editUserPassword(User $user = null, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder): Response {

        if ($user){
            if($user->getId() == $this->getUser()->getId()){

                $form = $this->createForm(ChangePasswordType::class);

                $form->handleRequest($request);
        
                if($form->isSubmitted() && $form->isValid()) {
        
                    $mdp = $form->get('plainPassword')->getData();
                    $mdp = $passwordEncoder->encodePassword($user, $mdp);
                    $this->getDoctrine()->getRepository(User::class)->upgradePassword($user, $mdp);    
                    $this->addFlash("success", "Le mot de passe a bien été changé.");

                    return $this->redirectToRoute("user_show", ['id'=>$user->getId()]);          
                }
        
                return $this->render("user/editPassword.html.twig", [
                    "formUser" => $form->createView()
                ]);

            } else {
                $this->addFlash("error", "Accès interdit.");
                return $this->redirectToRoute("home");
            }
        }else{
            $this->addFlash("error", "Cet administrateur n'existe pas.");
            return $this->redirectToRoute('home');
        }
    }



//                                                     ******************SUPPRESSIONS*****************
    

    /**
     * @Route("/user/{id}/delete", name="user_delete")
     */
    public function deleteUser(User $user = null, EntityManagerInterface $manager){
      
        if($this->getUser()){
            $comments = $user->getComments();
            foreach($comments as $comment){
                $manager->remove($comment);
            }
            $bibliotheques = $user->getBibliotheques();
            foreach ($bibliotheques as $bibliotheque) {
                $manager->remove($bibliotheque);
            }
            $subscribers = $user->getSubscribers();
            foreach ($subscribers as $subscriber){
                $manager->remove($subscriber);
            }
            $subscriptions = $user->getListSubscriptions();
            foreach ($subscriptions as $subscription){
                $manager->remove($subscription);
            }
            
            $manager->remove($user);
            $manager->flush();
        
            return $this->redirectToRoute('user_index');
        }else{
            return $this->redirectToRoute("home");
        }
    }

}
