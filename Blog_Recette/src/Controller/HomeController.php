<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Recipe;
use App\Data\SearchData;
use App\Form\SearchForm;
use App\Entity\Ingredient;
use App\Entity\RecipeLike;
use App\Entity\Bibliotheque;
use App\Entity\Subscription;
use App\Entity\CategoryRecipe;
use App\Form\CategoryRecipeType;
use App\Entity\CategoryIngredient;
use App\Form\CategoryIngredientType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryIngredientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class HomeController extends AbstractController
{
    
//                                                    ******************AFFICHAGES*****************
    
    
    /**
     * @Route("/show/bibliotheque/{id}", name="show_bibliotheque")
     */
    public function showBiblio(User $user = null, Bibliotheque $bibliotheque) {
        if($this->getUser()){
            $recipes = $this->getUser()->getBibliotheques();

            return $this->render('bibliotheque/showBiblio.html.twig', [
                'recipes' => $recipes,
                'user' => $user,  
                'bibliotheque' => $bibliotheque
            ]);
        } else {
            $this->addFlash("error", "incrivez-vous ou connectez-vous.");
            return $this->redirectToRoute('home');
        }
        
    }

     /**
     * @Route("/user/{id}/bibliotheque", name="bibliotheque_index")
     */
    public function indexBiblio(User $user = null){

        if($this->getUser()) {
            return $this->render('bibliotheque/bibliotheque.html.twig',[
                'user' => $user
            ]);
        } else {
            $this->addFlash("error", "incrivez-vous ou connectez-vous.");
            return $this->redirectToRoute('home');
        }
    }
    
    /**
     * @Route("/user/{id}/recipes", name="recipesUser_index")
     */
    public function indexRecipes(User $user = null){
        
        if($this->getUser()) {
            return $this->render('user/showRecipesUser.html.twig',[
                'user' => $user
            ]);
        }else {
            $this->addFlash("error", "incrivez-vous ou connectez-vous.");
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/user/{id}/subscriptions", name="abonnements_index", methods="GET")
     */
    public function showAbonnements(Subscription $subscription = null, User $user = null, EntityManagerInterface $manager)
    {   
        
        if ($this->getUser()) {
            return $this->render('user/subscriptions.html.twig', [
                "subscription" =>$subscription,
                "user" => $user
            ]);
        } else {
            $this->addFlash("error", "incrivez-vous ou connectez-vous.");
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/user/{id}/favoris", name="fav")
     */
    public function showFav(User $user = null, RecipeLike $recipeLike = null){
        
        if($this->getUser()){
            return $this->render('user/fav.html.twig', [
                "recipeLike" =>$recipeLike,
                "user" => $user

            ]);

        }else {
            $this->addFlash("error", "incrivez-vous ou connectez-vous.");
            return $this->redirectToRoute('home');
        }
    }

     /**
     * @Route("/user/{id}/subscribers", name="subscribers_index", methods="GET")
     */
    public function showAbonné(Subscription $subscription = null, User $user = null)
    {   
        
        if ($this->getUser()) {
            return $this->render('user/subscribers.html.twig', [
                "subscription" =>$subscription,
                "user" => $user
            ]);
        } else {
            $this->addFlash("error", "incrivez-vous ou connectez-vous.");
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/", name="home")
     */
    public function searchIndex(RecipeRepository $repo, Request $request){
        
        $data = new SearchData();//initialisation des données
        // $data->page = $request->get('page', 1);
        //creation formulaire qui utiliser search form 
        $form = $this->createForm(SearchForm::class, $data); // creation d'un autrre objet $data qui represente les données et qui pourra ete modifié
        $form->handleRequest($request);
        $recipes = $repo->findSearch($data); //recupere l'ensemble des données lié a une recherche
        return $this->render('home/index.html.twig', [
            'recipes' => $recipes,
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("/user/{id}/filActu", name="filActu", methods="GET")
     */
    public function filActualités(){
        if($this->getUser()){

            $recipes = $this->getDoctrine()->getRepository(Recipe::class)->findRecipesByUserSubscription($this->getUser());
            return $this->render('user/aboHome.html.twig', [
                "recipes" => $recipes,
            ]);
            // dd($recipes);
            
        } else {
            $this->addFlash("error", "Accès interdit.");
            return $this->redirectToRoute("home");
        }
    }

    /**
     * @Route("/categoriesIngredientList", name="catIngredientList")
     */
    public function categoriesIngredientIndex()
    {

        $ingredients = $this->getDoctrine()
                           ->getRepository(Ingredient::class)
                           ->getAll();
        return $this->render('categories/listIngr.html.twig', [
            'ingredients' => $ingredients,
        ]);
    }


//                                                    ******************AJOOUTS/EDITIONS*****************

    /**
     * @Route("/catRecipe/new", name="new_catRecipe")
     * @Route("/catRecipe/edit/{id}", name="edit_catRecipe")
     */
    public function newCatRecipe(CategoryRecipe $categoryRecipe = null, Request $request, ManagerRegistry $manager): Response {
        
        if ( ! $categoryRecipe){

            $categoryRecipe = new CategoryRecipe();
        }

        $form = $this->createForm(CategoryRecipeType::class, $categoryRecipe);
        $form->handleRequest($request);

        if($this->getUser()->isAdmin()){
            if($form->isSubmitted() && $form->isValid()) 
        {   
            $em = $manager->getManager();
            $em->persist($categoryRecipe);
            $em->flush();

            return $this->redirectToRoute('catRecipe_index');
        }
        } else {
            $this->addFlash("error", "Accès interdit.");
            return $this->redirectToRoute("home");
        }
        
        return $this->render('categories/addCatRecipe.html.twig', [
            'formCatRecipe' => $form->createView(),
            'categoryRecipe' => $categoryRecipe,
            'editMode' => $categoryRecipe->getId() !== null,
            
        ]);
    }


    /**
     * @Route("/catIngredient/new", name="new_catIngredient")
     * @Route("/catIngredient/edit/{id}", name="edit_catIngredient")
     */
    public function newCatIngredient(CategoryIngredient $categoryIngredient = null, Request $request, ManagerRegistry $manager): Response {
        
        if ( ! $categoryIngredient){

            $categoryIngredient = new CategoryIngredient();
        }

        $form = $this->createForm(CategoryIngredientType::class, $categoryIngredient);

        $form->handleRequest($request);
        if($this->getUser()->isAdmin()){
            if($form->isSubmitted() && $form->isValid()) 
            {   
                $em = $manager->getManager();
                $em->persist($categoryIngredient);
                $em->flush();

                return $this->redirectToRoute('catIngredient_index');
            }
        } else {
            $this->addFlash("error", "Accès interdit.");
            return $this->redirectToRoute("home");
        }  
        
        return $this->render('categories/addCatIngredient.html.twig', 
        [
            'formCatIngredient' => $form->createView(),
            'categoryIngredient' => $categoryIngredient,
            'editMode' => $categoryIngredient->getId() !== null,
        ]);
    }

//                                                    ******************SUPPRESSIONS*****************

    

}
