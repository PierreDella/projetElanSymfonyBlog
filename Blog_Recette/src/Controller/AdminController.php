<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\CategoryRecipe;
use App\Entity\CategoryIngredient;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{   
//                                                    ******************AFFICHAGES*****************

    /**
     * @Route("/categoriesrecipe", name="catRecipe_index")
     */
    public function categoriesRecipeIndex()
    {
        $repo = $this->getDoctrine()->getRepository(CategoryRecipe::class);
        $categories = $repo->findAll();
        return $this->render('admin/indexCatRecipe.html.twig', [
            'categories' => $categories,
        ]);
    }

     /**
     * @Route("/recipes", name="recipe_index")
     */
    public function index(RecipeRepository $repo)
    {
        // $repo = $this->getDoctrine()->getRepository(Recipe::class);
        $recipes = $repo->findAll();
        return $this->render('admin/recipes.html.twig', [
            'recipes' => $recipes,
        ]);
    }
    
    /**
     * @Route("/categoriesIngredient", name="catIngredient_index")
     */
    public function categoriesIngredientIndex()
    {
        $ingredients = $this->getDoctrine()
                           ->getRepository(Ingredient::class)
                           ->getAll();
        return $this->render('admin/indexCatIngredient.html.twig', [
            'ingredients' => $ingredients,
        ]);
    }

    /**
     * @Route("/users", name="user_index")
     */
    public function indexUsersAdmin()
    {   //si es Admin
        if($this->getUser()->isAdmin()){
            $repo = $this->getDoctrine()->getRepository(User::class);
            $users = $repo->findAll();
            return $this->render('admin/users.html.twig', [
                'users' => $users,
            ]);
        } else {
            $this->addFlash("error", "Accès interdit.");
            return $this->redirectToRoute("home");
        }   
    }

     /**
     * @Route("/adminHome", name="adminHome")
     */
    public function dashboardAdmin(){
        //Si est admin
        if($this->getUser()->isAdmin()){
            $users = $this->getDoctrine()->getRepository(User::class)->getAllOrder();
            $recipes = $this->getDoctrine()->getRepository(Recipe::class)->getAllOrder();
            return $this->render('admin/adminHome.html.twig', [
                "users" => $users,
                "recipes"=>$recipes
            ]);

        } else {
            $this->addFlash("error", "Accès interdit.");
            return $this->redirectToRoute("home");
        }
    }

//                                                    ******************AJOUTS/EDITIONS*****************



//                                              ******************SUPPRESSIONS*****************
     

    /**
     * @Route("/categoryRecipe/{id}/delete", name="categoryRecipe_delete")
     */
    public function deleteCategoryRecipe(CategoryRecipe $categoryRecipe = null, EntityManagerInterface $manager){
        
        if($this->getUser()->isAdmin()){
            $recipes = $categoryRecipe->getRecipes();
            foreach($recipes as $recipe){
                $manager->remove($recipe);
            }
            $manager->remove($categoryRecipe);
            $manager->flush();
        
            return $this->redirectToRoute('home');
        }
    }

   /**
     * @Route("/recipe/{id}/delete", name="recipe_delete_admin")
     */
    public function deleteRecipeAdmin(Recipe $recipe = null, EntityManagerInterface $manager){
      
        if($this->getUser() == $recipe->getUser() or $this->IsGranted('ROLE_ADMIN')){
            $comments = $recipe->getComments();
            foreach($comments as $comment){
                $manager->remove($comment);
            }
            $bibliotheques = $recipe->getBibliotheques();
            foreach ($bibliotheques as $bibliotheque) {
                $manager->remove($bibliotheque);
            }
            $likes = $recipe->getLikes();
            foreach ($likes as $like) {
                $manager->remove($like);
            }
            // $user = $recipe->getUser()
            $manager->remove($recipe);
            $manager->flush();
        
            return $this->redirectToRoute('recipe_index');
        }else{
            $this->addFlash("error", "Action interdite");
            return $this->redirectToRoute("detailRecipe", ["id"=>$recipe->getId()]);
        }
    }
    /**
     * @Route("/ingredient/{id}/delete", name="ingredient_delete_admin")
     */
    public function deleteIngredient(Ingredient $ingredient = null, EntityManagerInterface $manager){
        if($this->IsGranted('ROLE_ADMIN')){
            // dd($ingredient);
            $compositions = $ingredient->getCompositions();
            // dd($compositions);
            foreach ($compositions as $composition) {
                $manager->remove($composition);
            }
            // $categories = $ingredient->getCategory();
            // foreach($categories as $category){
            //     $manager->remove($category);
            // }
            $manager->remove($ingredient);
            $manager->flush();
        
            return $this->redirectToRoute('catIngredient_index');
        }else{
            $this->addFlash("error", "Action interdite");
            return $this->redirectToRoute("home");
        }
    }
    /**
     * @Route("/user/{id}/delete", name="admin_user_delete")
     */
    public function adminDeleteUser(User $user = null, EntityManagerInterface $manager){
       
        if($this->getUser()->isAdmin() or $this->getUser() == $user){
            
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
            $manager->remove($user); 
            $manager->flush();
        
            return $this->redirectToRoute('user_index');
        }else{
            $this->addFlash("error", "Suppression non autorisé.");
            return $this->redirectToRoute("home");
        }
    }
}
