<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Comment;
use App\Form\RecipeType;
use App\Form\CommentType;
use App\Entity\Ingredient;
use App\Entity\Composition;
use App\Form\IngredientType;
use App\Form\CompositionType;
use App\Form\RecipeSearchType;
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
                'createdAt' => $comment->getCreatedAt()
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
     *@Route("/recipe/add", name="add_recipe")
     * 
     */
    public function addRecipeStep1(Request $request, RecipeRepository $repo){
        
        //On recupere la session
        $session = $request->getSession();
        //On crée une nouvelle recette
        $recipe = new Recipe();
        //La recette aura comme user la personne connecté
        $recipe->setUser($this->getUser());
        //Si la recette a deja un nom, on pourra le modifier
        if($session->has("name")){
            $recipe->setName($session->get("recipe_name", null));
        }
        //On crée un formulaire, où on remove un certain nombre de fields
        $form = $this->createForm(RecipeType::class, $recipe);
        
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){   
           
            
            return $this->redirectToRoute('add_recipe');

        }

       return $this->render('recipe/addRecipe.html.twig', [
           'form' => $form->createView(),
           'recipe' => $recipe
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

     /**
     *@Route("/recipe/{id}/comment/add/", name="comment_add")
     */
    public function addComment(Recipe $recipe = null, Request $request, ManagerRegistry $manager, Comment $comment = null): Response {
        
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){ 
            
            $em = $manager->getManager();
            $recipe->addComment($comment);
            $comment->setUser($this->getUser());//this get user recupere les infos de la session
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('detailRecipe', ["id"=>$recipe->getId()]);
        }

        return $this->render('recipe/addComment.html.twig', [
            'formComment' => $form->createView(),
            'recipe' => $recipe
            
        ]);
    }

}
