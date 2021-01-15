<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Recipe;
use App\Form\UserType;
use App\Entity\RecipeLike;
use App\Entity\Bibliotheque;
use App\Entity\Subscription;
use App\Form\BibliothequeType;
use App\Form\ChangePasswordType;
use App\Repository\RecipeLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\SubscriptionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{


//                                                    ******************AFFICHAGES*****************

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
     * @Route("/user/{id}/subscriptions", name="abonnements_index", methods="GET")
     */
    public function showAbonnements(Subscription $subscription = null, User $user = null, EntityManagerInterface $manager)
    {   
        
        if ($user) {
            return $this->render('user/subscriptions.html.twig', [
                "subscription" =>$subscription,
                "user" => $user
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/user/{id}/favoris", name="fav")
     */
    public function showFav(User $user = null, RecipeLike $recipeLike = null){
        
        
        
        if($user){

            return $this->render('user/fav.html.twig', [
                "recipeLike" =>$recipeLike,
                "user" => $user

            ]);

        }else {

            return $this->redirectToRoute('home');
        }
    }

     /**
     * @Route("/user/{id}/subscribers", name="subscribers_index", methods="GET")
     */
    public function showAbonné(Subscription $subscription = null, User $user = null)
    {   
        
        if ($user) {
            return $this->render('user/subscribers.html.twig', [
                "subscription" =>$subscription,
                "user" => $user
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/user/{id}/filActu", name="filActu", methods="GET")
     */
    public function filActualités( User $user = null, EntityManagerInterface $manager)
    {   
        $subscribers = $this->getUser()->getSubscribers();
        $subscriptions = $this->getUser()->getListSubscriptions();
        
        if ($user) {
            return $this->render('user/actu.html.twig', [
                'user' => $user,
                "subscriptions" => $subscriptions,
                "subscribers" => $subscribers,
                
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
     * @Route("/{id}/publicRecipe", name="recipe_lock")
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
    public function makeSubscription(User $target, EntityManagerInterface $manager) : Response{
        
        
        $subscription = new Subscription();
        $subscription->setSubscriber($this->getUser());
        $subscription->setTargetUser($target); 
        $manager->persist($subscription);
        $manager->flush(); 

        return $this->redirectToRoute("abonnements_index", ["id"=>$this->getUser()->getId()]);
    }


   

    /** Permet de liker ou non une recette
     * @Route("/recipe/{id}/like", name="recipe_like")
     */
    public function like(Recipe $recipe, EntityManagerInterface $manager, RecipeLikeRepository $likeRepo) : Response{
        
        $user = $this->getUser();
        //Si l'utilisateur est anonyme et qu'il essaye de like une recette 
        if(!$user) return $this->json([
            'code' => 403,
            'message' => "Unauthorized"
        ], 403);
        //Si j'ai deja aimé supp le j'aime
        if($recipe->isLikedByUser($user)){
            //On lui demande de retrouver le like du user et de la recette
            $like = $likeRepo->findOneBy([
                'recipe' => $recipe,
                'user' => $user
            ]);

            $manager->remove($like);
            $manager->flush();
            //On retourne l'info au js
            return $this->json([
                'code' => 200,
                'message' => 'Like supprimé',
                'likes' => $likeRepo->count(['recipe' => $recipe])
            ], 200); //donne le statu http
        }
        $like = new RecipeLike();
        $like->setRecipe($recipe)
             ->setUser($user);

             $manager->persist($like);
             $manager->flush();

             return $this->json([
                'code' => 200,
                'message' => 'Like ajouté',
                'likes' => $likeRepo->count(['recipe' => $recipe])
            ], 200); //donne le statu http

        return $this->json(['code' => 200, ''], 200);
    }

    



//                                                     ******************SUPPRESSIONS*****************
    

    /**
     * @Route("/user/{id}/delete", name="user_delete")
     */
    public function deleteUser(User $user = null, EntityManagerInterface $manager){
       
        if($this->getUser()->isAdmin()){
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
            $likes = $user->getLikes();
            foreach ($likes as $like) {
                $manager->remove($like);
            }
            $manager->remove($user);
            $manager->flush();
        
            return $this->redirectToRoute('user_index');
        }else{
            $this->addFlash("error", "Suppression non autorisé.");
            return $this->redirectToRoute("home");
        }
    }
    /**
     *  @Route("/delete/bibliotheque/{id}", name="delete_bibliotheque")
     */
    public function deleteBibliotheque(Bibliotheque $bibliotheque = null, Request $request, EntityManagerInterface $manager){
        
            $recipes = $bibliotheque->getRecipes();
            foreach ($bibliotheque->getRecipes() as $recipes) {
                $bibliotheque->removeRecipe($recipes);
            }
            $manager->remove($bibliotheque);
            $manager->flush();

            return $this->redirectToRoute('bibliotheque_index', ["id"=>$this->getUser()->getId()]);
      
    }

    /** 
     * @Route("/unsubscribe/{id}", name="delete_following")
     */
    public function unsubsciption(Subscription $subscription, Request $request, EntityManagerInterface $manager){

        
        $manager->remove($subscription);
        $manager->flush();

        return $this->redirectToRoute("abonnements_index", ["id"=>$this->getUser()->getId()]);
    }
    
}
