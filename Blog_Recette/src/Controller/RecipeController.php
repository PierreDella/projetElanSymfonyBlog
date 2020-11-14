<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Comment;
use App\Form\RecipeType;
use App\Entity\Ingredient;
use App\Entity\Composition;
use App\Form\IngredientType;
use App\Form\CompositionType;
use App\Entity\CategoryIngredient;
use App\Repository\RecipeRepository;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{
    /**
     * @Route("/recipes", name="recipe_index")
     */
    public function index(RecipeRepository $repo)
    {
        // $repo = $this->getDoctrine()->getRepository(Recipe::class);
        $recipes = $repo->findAll();
        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }
    

    /**
     * @Route("/recipe/composition/{id}", name="detailRecipe")
     */
    public function indexComposition(Recipe $recipe, Comment $comment) {
        
  
        //Categorie
        $categories = $recipe->getCategories();
        $recipeCategories = [];
        foreach ($categories as $category) {
            $recipeCategories[] = [
                "name" => $category->getName()
            ];
        }

        $compos = $recipe->getCompositions();
        $ingredients = [];
        foreach($compos as $compo){
            $ingredients[] = [
                "name" => $compo->getIngredient()->getName(),
                "unit" => $compo->getUnit(),
                "quantity" => $compo->getQuantity(),
                "category" => $compo->getIngredient()->getCategory()->getName()
            ];  
        }

        $comments = $recipe->getComments();
        $recipeComments = [];
        foreach ($comments as $comment) {
            $recipeComments[] = [
                'pseudo' => $comment->getUser()->getPseudo(),
                'post' => $comment->getPost(),
                // 'createdAt' => $comment->getCreatedAt()
            ];
        }
        return $this->render('recipe/show.html.twig', [
            'recipe'      => $recipe,
            'ingredients' => $ingredients,
            'recipeCategories' => $recipeCategories,
            'recipeComments' => $recipeComments
            
            
        ]);
        
    }

    /**
     *@Route("/recipe/add", name="begin_recipe")
     * @Route("/recipe/edit/{id}", name="finish_recipe") 
     */
    public function newRecipe(Recipe $recipe = null, Request $request, ManagerRegistry $manager): Response {
        
        $recipe = new Recipe();
        $recipe->setUser($this->getUser());
        $formFirst = $this->createForm(RecipeType::class, $recipe);
        
        $formFirst->handleRequest($request);

        if($formFirst->isSubmitted() && $formFirst->isValid()){
           
            $em = $manager->getManager();
            $em->persist($recipe);
            $em->flush();

            return $this->redirectToRoute('add_composition');
        }
       return $this->render('recipe/new.html.twig', [
           'formFirst' => $formFirst->createView(),
           'formSecond' => $recipe->getId() !== null,
           'recipe' => $recipe
       ]);
    }

    /**
     * @Route("/composition/add", name="add_composition")
     */
    public function newComposition(Recipe $recipe, Composition $composition, Request $request, EntityManagerInterface $manager): Response {

        $composition = new Composition();
        $composition->setRecipe($recipe);
        $formComposition = $this->createForm(CompositionType::class, $composition);

        $formComposition->handleRequest($request);  

        if($formComposition->isSubmitted() && $formComposition->isValid()){
            $manager->persist($composition);
            $manager->flush();
        
            return $this->redirectToRoute('finish_recipe');
        }

        return $this->render('composition/add.html.twig', [
            'formComposition' => $formComposition->createView()
        ]);
    }


    /**
     * @Route("/ingredient/new", name="new_ingredient")
     * @Route("/ingredient/edit/{id}", name="edit_ingredient")
     */
    public function newIngredient(Ingredient $ingredient = null, Request $request, EntityManagerInterface $manager): Response {
        
        $ingredient = new Ingredient();

        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
           
            $manager->persist($ingredient);
            $manager->flush();

            return $this->redirectToRoute('recipe_index');
        }
        return $this->render('ingredient/add.html.twig', [
            'formIngredient' => $form->createView(),
            'ingredient' => $ingredient
        ]);
    }






    // public function search(IngredientRepository $ingredient){
        
    //     $ingredient = $repository->findSearch();
    //     return $this->render('ingredient/add.html.twig', [
    //         'ingredient' => $ingredient,
    //     ]);
    // }
}
