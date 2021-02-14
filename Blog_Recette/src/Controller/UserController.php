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
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{


//                                                    ******************AFFICHAGES*****************
    /**
     * @Route("/user/{id}", name="user_show", methods="GET")
     */
    public function show(User $user = null)
    {   
        if ($this->getUser()) {
            return $this->render('user/show.html.twig', [
                'user' => $user,
                
            ]);
        } else {
            $this->addFlash("error", "incrivez-vous ou connectez-vous.");
            return $this->redirectToRoute('home');
        }
    }
    


//                                                    ******************EDITIONS/AJOUTS*****************

    /**
     * @Route("/{id}/public", name="biblio_lock")
     * 
     */
    public function lock(Bibliotheque $bibliotheque, EntityManagerInterface $manager){
        //Gestion des droits d'accès
        if($this->getUser()->isAdmin() || $this->getUser() == $bibliotheque->getUser()){
                $closeState = $bibliotheque->getPublique() ? false : true; //ternaire
                //set le nouvel état de la biblio
                $bibliotheque->setPublique($closeState);
                $manager->persist($bibliotheque);
                $manager->flush($bibliotheque);

                return $this->redirectToRoute('bibliotheque_index', ["id"=>$this->getUser()->getId()]);
            // }
        }
        //Si les droits d'accès ne sont pas donnés
        $this->addFlash("error", "vous n'avez pas les autorisations nécessaires.");
        return $this->redirectToRoute('home');
    }

    

    /**
     * @Route("/{id}/publicRecipe", name="recipe_lock")
     * 
     */
    public function lockRecipe(Recipe $recipe, EntityManagerInterface $manager){

        if($this->getUser()->isAdmin() || $this->getUser() == $recipe->getUser()){
            $closeState = $recipe->getPublished() ? false : true;
            $recipe->setPublished($closeState);
            $manager->persist($recipe);
            $manager->flush($recipe);

            return $this->redirectToRoute('recipesUser_index', ["id"=>$this->getUser()->getId()]);
        }
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/add/bibliotheque", name="add_bibliotheque")
     * @Route("/edit/bibliotheque/{id}", name="edit_bibliotheque")
     */
    public function createBibliotheque(Bibliotheque $bibliotheque = null, Request $request, ManagerRegistry $manager): Response {
            
        if(!$bibliotheque){ //Si pas de bibliotheque
            $bibliotheque = new Bibliotheque();
        }
        //recupère l'objet de request
        $recipeId = $request->query->get("recipeid");
        //Si recette on trouve l'id de la recette et on l'ajoute dans la biblio
        if ($recipeId){
            $recipe = $manager->getRepository(Recipe::class)->findOneBy(["id" => $recipeId]);
            $bibliotheque->addRecipe($recipe);
        }

        $form = $this->createForm(BibliothequeType::class, $bibliotheque);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $em = $manager->getManager();
            $bibliotheque->setUser($this->getuser());
            $em->persist($bibliotheque);
            $em->flush();

            return $this->redirectToRoute('bibliotheque_index', ["id"=>$this->getUser()->getId()]);
        }   

        return $this->render('bibliotheque/addBibliotheque.html.twig', [

            'editMode' => $bibliotheque->getId() !== null,
            'formBiblio'=> $form->createView(),
            'bibliotheque' => $bibliotheque,

        ]);
    }


    /**
     * @Route("/add-recipe-collection", name="addRecipeToCollection")
     */
    public function addRecipeToCollection(EntityManagerInterface $manager, Request $request){
        //requetes pour récupérer l'id de la bibli selec et de la recette
        $recipeId = $request->query->get("recipeid");
        $biblioId = $request->query->get("biblioid");
        //On retrouve la bibli et la recette dans la BDD
        $bibliotheque = $manager->getRepository(Bibliotheque::class)->findOneBy(["id" => $biblioId]);
        $recipe = $manager->getRepository(Recipe::class)->findOneBy(["id" => $recipeId]);
        //Ajout de la recette dans biblio
        $bibliotheque->addRecipe($recipe);
        
        $manager->flush();

        return $this->redirectToRoute('bibliotheque_index', ["id"=>$this->getUser()->getId()]);
    }

    /** 
     * @Route("/subscribe/{id}", name="add_following")
     */
    public function makeSubscription(User $target, EntityManagerInterface $manager) : Response{
        
        if($this->getUser()){
            $subscription = new Subscription();
            $subscription->setSubscriber($this->getUser());
            $subscription->setTargetUser($target); 
            $manager->persist($subscription);
            $manager->flush(); 

            return $this->redirectToRoute("abonnements_index", ["id"=>$this->getUser()->getId()]);
        }else{
            $this->addFlash("error", "vous n'avez pas les autorisations nécessaires.");
        }
        
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

    /**
     * @Route("/user/{id}/edit", name="user_edit")
     */
    public function editUser(User $user = null, Request $request, EntityManagerInterface $manager, SluggerInterface $slugger): Response {

        if ($user){
            if($user->getId() == $this->getUser()->getId()){
                
                $form = $this->createForm(UserType::class, $user);

                $form->handleRequest($request);
        
                if($form->isSubmitted() && $form->isValid()) {
                    /** @var UploadedFile $picture */
                    $picture = $form->get('picture')->getData();
                
                    if ($picture) {
                        $originalPicture = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                        $safePicture = $slugger->slug($originalPicture);
                        $newPicture = $safePicture.'-'.uniqid().'.'.$picture->guessExtension();
                        
                        try {
                            $picture->move(
                                $this->getParameter('pictures_directory'),
                                $newPicture
                            );
                        } catch (FileException $e) {
                        
                        }
                        
                        $user->setPicture($newPicture);
                        $manager->persist($user);
                        $manager->flush();
                        $this->addFlash("success", "Le profil a bien été édité.");
                        return $this->redirectToRoute("user_show", ['id'=>$user->getId()]);
                    }
                }
        
                return $this->render("user/edit.html.twig", [
                    "formUser" => $form->createView(),
                    'user' => $user
                ]);

            } else {
                $this->addFlash("error", "Accès interdit.");
                return $this->redirectToRoute("home");
            }
        }else{
            $this->addFlash("error", "Cet ustilisateur n'existe pas.");
            return $this->redirectToRoute('home');
        }
       

    }

//                                                     ******************SUPPRESSIONS*****************
    

    /**
     * @Route("/user/{id}/delete", name="user_delete")
     */
    public function deleteUser(User $user = null, EntityManagerInterface $manager, Request $request, TokenStorageInterface $tokenStorage): Response {
       
        if($this->getUser() == $user){
            // $session = new Session();
            // $session->invalidate();

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
            // if (condition) {
            //     # code...
            // }
            
           
               
                
                // $manager = $this->getDoctrine()->getManager();
                // $user = $this->getUser();
                // $this->container->get('security.token_storage')->setToken(null);
                // $tokenStorage->setToken(null);
                // $session->invalidate();
                $manager->remove($user);
                $manager->flush();
            
            
            
            return $this->redirectToRoute('app_login');
        }else{
            $this->addFlash("error", "Suppression non autorisé.");
            return $this->redirectToRoute("home");
        }
    }
    /**
     *  @Route("/delete/bibliotheque/{id}", name="delete_bibliotheque")
     */
    public function deleteBibliotheque(Bibliotheque $bibliotheque = null, Request $request, EntityManagerInterface $manager){
        
        if($this->getUser()->isAdmin() or $this->getUser() == $bibliotheque->getUser()){
            $recipes = $bibliotheque->getRecipes();

            foreach ($bibliotheque->getRecipes() as $recipes) {
                $bibliotheque->removeRecipe($recipes);
            }

            $manager->remove($bibliotheque);
            $manager->flush();

            return $this->redirectToRoute('bibliotheque_index', ["id"=>$this->getUser()->getId()]);
        } else {
            $this->addFlash("error", "Suppression non autorisé.");
            return $this->redirectToRoute('bibliotheque_index', ["id"=>$this->getUser()->getId()]);
        }
    }

    /** 
     * @Route("/unsubscribe/{id}", name="delete_following")
     */
    public function unsubsciption(Subscription $subscription, Request $request, EntityManagerInterface $manager){

        if($this->getUser()->isAdmin() or $this->getUser() == $subscription->getSubscriber()){
            $manager->remove($subscription);
            $manager->flush();

            return $this->redirectToRoute("abonnements_index", ["id"=>$this->getUser()->getId()]);
        } else {
            $this->addFlash("error", "Suppression non autorisé.");
            return $this->redirectToRoute('bibliotheque_index', ["id"=>$this->getUser()->getId()]);
        }
        
    }
    
    /**
     * @Route("/bibliotheque/{bibliotheque_id}/programme/unsubscribe/{recipe_id}", name="remove_recipe")
     * @ParamConverter("bibliotheque", options={"id" = "bibliotheque_id"})
     * @ParamConverter("recipe", options={"id" = "recipe_id"})
     */
    public function removeRecipe(Recipe $recipe = null, Bibliotheque $bibliotheque = null, EntityManagerInterface $manager){
        if ($this->getUser()) {
            if ($bibliotheque && $recipe){
                // dump($bibliotheque);
                // dump($recipe);
                $bibliotheque->removeRecipe($recipe);
                $manager->persist($bibliotheque);
                $manager->flush();
    
                return $this->redirectToRoute('show_bibliotheque', ['id' => $bibliotheque->getId()]);
            }else{
                $this->addFlash("error", "Veuillez vérifier la bibliotheque et/ou la recette.");
                return $this->redirectToRoute('home');
            }
        }
        
    } 
    
}
