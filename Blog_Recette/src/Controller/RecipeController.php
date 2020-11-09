<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Composition;
use App\Repository\RecipeRepository;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/recipe/new", name="new_recipe")
     */
    public function newRecipe() {
        $recipe = new Recipe();
        $recipe->setUser($this->getUser());
        $form = $this->createForm(RecipeType::class, $recipe);
        // $form
        return $this->render('recipe/new.html.twig');
    }
}
