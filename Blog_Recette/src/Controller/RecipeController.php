<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Comment;
use App\Form\RecipeType;
use App\Form\CommentType;
use App\Entity\Ingredient;
use App\Entity\RecipeLike;
use App\Entity\Composition;
use App\Entity\Subscription;
use App\Form\IngredientType;
use App\Form\CompositionType;
use App\Entity\CategoryRecipe;
use App\Form\CategoryRecipeType;
use App\Entity\CategoryIngredient;
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
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RecipeController extends AbstractController
{
//                                                    ******************AFFICHAGES*****************
    /**
     * @Route("/recipe/composition/{id}", name="detailRecipe")
     */
    public function indexComposition(Recipe $recipe, Comment $comment = null, Subscription $subscription = null, Request $request, EntityManagerInterface $manager) {
        
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

        return $this->render('recipe/show.html.twig', [
            'recipe'      => $recipe,
            'ingredients' => $ingredients,
            'recipeCategories' => $recipeCategories,
            'formComment' => $form->createView(),
            'subscription' => $subscription,
            
            
        ]);
        
    }
//                                                 ******************EDITIONS/AJOUTS*****************


    /**
    * @Route("/recipe/add", name="add_recipe")
    * @Route("/recipe/edit/{id}", name="edit_recipe")
    */
    public function addRecipe(ManagerRegistry $manager, Request $request, Recipe $recipe = null, SluggerInterface $slugger): Response{


        if ( ! $recipe){
            //On crée une nouvelle recette
            $recipe = new Recipe();
        }
        //La recette aura comme user la personne connecté
        $recipe->setUser($this->getUser());
        //On crée un formulaire
        $form = $this->createForm(RecipeType::class, $recipe);
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
                        $this->getParameter('picturesRecipe_directory'),
                        $newPicture
                    );
                } catch (FileException $e) {
                   
                }
                
                $recipe->setPicture($newPicture);
                $time = ($request->request->get('recipe'));

                if (($time['preparationTime']) >= $time['cookingTime']){     
                
                    $em = $manager->getManager();
                    $em->persist($recipe);
                    $em->flush();

                    return $this->redirectToRoute('home');

                }else{
                    $this->addFlash("error", "Merci de mettre un temps de préparation inférieur au temps de cuisson");
                }
            }
        }
            return $this->render('recipe/addRecipe.html.twig', 
            [
                'form' => $form->createView(),
                'recipe' => $recipe,
                'editMode' => $recipe->getId() !== null,
            ]);
    }

    /**
     * @Route("/ingrList", name="ingrList")
     */
    public function ingrList(Request $request, EntityManagerInterface $manager){
        //on recupere l'id du form dans la vue
        $idCat = $request->query->get('idcat');
        //on trouve la catégorie lié à l'id
        $categorie = $manager->getRepository(CategoryIngredient::class)->findOneBy(['id' => $idCat]);
        // on retrouve les ingredients en liens avec la catégorie en se servant de requete dans le repository
        $ingredients = $manager->getRepository(Ingredient::class)->findByCategorie($categorie);
        //code html des ingrédients sera chaine de caractere vide par defaut
        $html = "";
        //qu'on remplira    
        foreach($ingredients as $i){  //boucle qui donnera a chaque ingrédient leurs valeurs repésenté par leurs id
            $html.= "<option ";
            $html.= "value='".$i->getId()."'>";
            $html.= $i->getName(); // recupere leur nom 
            $html.= "</option>";
        }
        //donne valeur par defaut quand aucun ingredient dans une categorie
        if(empty($ingredients))
            $html = "<option value='0'>Aucun ingrédient dans cette catégorie...</option>";
        
        
        return new Response($html);

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

            return $this->redirectToRoute('home');
        }
        return $this->render('ingredient/add.html.twig', [
            'formIngredient' => $form->createView(),
            'ingredient' => $ingredient,
            
        ]);
    }


//                                              ******************SUPPRESSIONS*****************
    
    /**
     * @Route("/recipe/{id}/delete", name="recipe_delete")
     */
    public function deleteRecipe(Recipe $recipe = null, EntityManagerInterface $manager){
      
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
        
            return $this->redirectToRoute('home');
        }else{
            $this->addFlash("error", "Action interdite");
            return $this->redirectToRoute("detailRecipe", ["id"=>$recipe->getId()]);
        }
    }

    
    
}
