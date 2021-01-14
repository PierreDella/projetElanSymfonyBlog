<?php

namespace App\Controller;

use App\Entity\User;
use App\Data\SearchData;
use App\Form\SearchForm;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    
//                                                    ******************AFFICHAGES*****************
    /**
     * @Route("/home", name="home")
     */
    public function searchIndex(RecipeRepository $repo, Request $request){
        
        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);
        $recipes = $repo->findSearch($data);
        return $this->render('home/index.html.twig', [
            'recipes' => $recipes,
            'form' => $form->createView(),
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

    /**
     * @Route("/categoryRecipe/{id}/delete", name="categoryRecipe_delete")
     */
    public function deleteCategoryRecipe(CategoryRecipe $categoryRecipe = null, EntityManagerInterface $manager){
        
        if($this->getUser()){
            $recipes = $categoryRecipe->getRecipes();
            foreach($recipes as $recipe){
                $manager->remove($recipe);
            }
            $manager->remove($categoryRecipe);
            $manager->flush();
        
            return $this->redirectToRoute('home');
        }
    }

}
