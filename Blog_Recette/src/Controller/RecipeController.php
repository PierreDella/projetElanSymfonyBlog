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
use App\Entity\CategoryRecipe;
use App\Form\CategoryRecipeType;
use App\Entity\CategoryIngredient;
use App\Entity\RecipeLike;
use App\Form\CategoryIngredientType;
use App\Repository\RecipeRepository;
use Doctrine\Persistence\ObjectManager;
use App\Repository\IngredientRepository;
use App\Repository\RecipeLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CategoryRecipeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{
//                                                    ******************AFFICHAGES*****************

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
     * @Route("/categoriesIngredient", name="catIngredient_index")
     */
    public function categoriesIngredientIndex()
    {
        $repo = $this->getDoctrine()->getRepository(CategoryIngredient::class);
        $categories = $repo->findAll();
        return $this->render('admin/indexCatIngredient.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/recipe/composition/{id}", name="detailRecipe")
     */
    public function indexComposition(Recipe $recipe, Comment $comment = null, Request $request, EntityManagerInterface $manager) {
        
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){ 

            $comment->setPost($form->get('post')->getData());
            $comment->setUser($this->getUser());
            $comment->setRecipe($recipe);

            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('detailRecipe', ["id"=>$recipe->getId()]);
        }
  
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
                'createdAt' => $comment->getCreatedAt(),
                'id' => $comment->getId()
            ];
        }
        return $this->render('recipe/show.html.twig', [
            'recipe'      => $recipe,
            'ingredients' => $ingredients,
            'recipeCategories' => $recipeCategories,
            'recipeComments' => $recipeComments,
            'formComment' => $form->createView(),
            
            
        ]);
        
    }
//                                                 ******************EDITIONS/AJOUTS*****************


    /**
     * @Route("{id}/editComment", name="comment_edit")
     */
    public function editComment(Comment $comment, Request $request, EntityManagerInterface $manager){

        if($this->getUser() == $comment->getUser() || $this->getUser()->isAdmin()){
            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $comment->setPost($form->get('post')->getData());
                $comment->setUser($this->getUser());
                $comment->setRecipe($comment->getRecipe());

                $manager->persist($comment);
                $manager->flush();

                return $this->redirectToRoute('detailRecipe',  ['id' => $comment->getRecipe()->getId() ]);
            }
            return $this->render('recipe/addComment.html.twig', [
                'formComment' => $form->createView()
            ]);
        }
        return $this->redirectToRoute('topics');

    }


    /**
     *@Route("/recipe/add", name="add_recipe")
     *@Route("/recipe/edit/{id}", name="edit_recipe")
     */
    public function addRecipe(ManagerRegistry $manager, Request $request, Recipe $recipe = null): Response{
        
        if ( ! $recipe){
            //On crée une nouvelle recette
            $recipe = new Recipe();
        }        
        //La recette aura comme user la personne connecté
        $recipe->setUser($this->getUser());
        //On crée un formulaire, où on remove un certain nombre de fields
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){   
            $time = ($request->request->get('recipe'));
            if (($time['preparationTime']) > $time['cookingTime']){ //securité
                
                $em = $manager->getManager();
                $em->persist($recipe);
                $em->flush();

                return $this->redirectToRoute('recipe_index');

            }else{
                $this->addFlash("error", "Merci de mettre un temps de préparation inférieur au temps de cuisson");
            }
            
        }
       return $this->render('recipe/addRecipe.html.twig', 
       [
           'form' => $form->createView(),
           'recipe' => $recipe,
           'editMode' => $recipe->getId() !== null,
       ]);
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

    /** Permet d'ajouter un ingredient dans la liste des ingrédients
     * @Route("/ingredient/new", name="new_ingredient")
     * 
     */
    public function addIngredient(Ingredient $ingredient = null, Request $request, EntityManagerInterface $manager): Response {
        
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
            'ingredient' => $ingredient,
            
        ]);
    }

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


        if($form->isSubmitted() && $form->isValid()) 
        {   
            $em = $manager->getManager();
            $em->persist($categoryRecipe);
            $em->flush();

            return $this->redirectToRoute('catRecipe_index');
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
        if($form->isSubmitted() && $form->isValid()) 
        {   
            $em = $manager->getManager();
            $em->persist($categoryIngredient);
            $em->flush();

            return $this->redirectToRoute('catIngredient_index');
        }
        return $this->render('categories/addCatIngredient.html.twig', 
        [
            'formCatIngredient' => $form->createView(),
            'categoryIngredient' => $categoryIngredient,
            'editMode' => $categoryIngredient->getId() !== null,
        ]);
    }


//                                              ******************SUPPRESSIONS*****************
    
    
    /**
     * @Route("{id}/deleteComment", name="coment_delete")
     */
    public function deleteComment(Comment $comment, EntityManagerInterface $manager){
        
        if($this->getUser()){
            if($this->getUser()->isAdmin() || $this->getUser() == $comment->getUser()){
                $recipeId = $comment->getRecipe()->getId();
                $manager->remove($comment);
                $manager->flush();

                return $this->redirectToRoute('detailRecipe', ['id' => $recipeId]);
            }
        }
        return $this->redirectToRoute('recipe_index');
    }


    /**
     * @Route("/recipe/{id}/delete", name="recipe_delete")
     */
    public function deleteRecipe(Recipe $recipe = null, EntityManagerInterface $manager){
      
        if($this->getUser()){
            $comments = $recipe->getComments();
            foreach($comments as $comment){
                $manager->remove($comment);
            }
            $bibliotheques = $recipe->getBibliotheques();
            foreach ($bibliotheques as $bibliotheque) {
                $manager->remove($bibliotheque);
            }
        
            $manager->remove($recipe);
            $manager->flush();
        
            return $this->redirectToRoute('recipesUser_index', ['id' => $this->getUser()->getId()]);
        }else{
            $this->addFlash("error", "Cette recette n'existe pas.");
            return $this->redirectToRoute("recipesUser_index");
        }
    }

    
    
}
