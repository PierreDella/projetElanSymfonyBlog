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
     *@Route("/recipe/step1", name="add_recipe_step_1")
     * 
     */
    public function addRecipeStep1(Request $request){
        
        //On recupere la session
        $session = $request->getSession();
        //On crée une nouvelle recette
        $recipe = new Recipe();
        //Si la recette a deja un nom, on pourra le modifier
        if($session->has("recipe_name")){
            $recipe->setName($session->get("recipe_name", null));
        }
        //On crée un formulaire, où on remove un certain nombre de fields
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->remove('picture');
        $form->remove('cookingTime');
        $form->remove('preparationTime');
        $form->remove('steps');
        $form->remove('instructions');
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            //La recette aura comme user la personne connecté
            $recipe->setUser($this->getUser());

            //En cas de retour en arrriere on permet la modification des fields voulu
            $session->set('recipe_name', $recipe->getName());
            $session->set('recipe_description', $recipe->getName());
            $session->set('recipe_nbPerson', $recipe->getName());
            $session->set('recipe_categories', $recipe->getName());

            return $this->redirectToRoute('add_recipe_step_2');

        }

       return $this->render('recipe/step1.html.twig', [
           'form' => $form->createView()
       ]);
    }

    /**
     * @Route("/composition/step2", name="add_recipe_step_2")
     */
    public function addRecipeStep2(Request $request, Recipe $recipe) {
        
        //On recupere la session
        $session = $request->getSession();
        //Si la recette n'a pas de nom on nous renvoie à la premiere etape
        if(!$session->has('recipe_name')){
            return $this->redirectToRoute('add_recipe_step_1');

        } else {

            //On crée un nouvelle composition
            $composition = new Composition();

            $composition->getRecipe($recipe);
            

        }
        
       
        $formComposition = $this->createForm(CompositionType::class, $composition);

        $formComposition->handleRequest($request);  

        if($formComposition->isSubmitted() && $formComposition->isValid()){
           
        
            return $this->redirectToRoute('finish_recipe');
        }

        return $this->render('composition/add.html.twig', [
            'formComposition' => $formComposition->createView()
        ]);
    }

    /**
     * @Route("/recipe/step3", name="add_recipe_step_3")
     */
    public function addRecipeStep3() {
       
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
