<?php

namespace App\Controller;

use App\Entity\User;
use App\Data\SearchData;
use App\Form\SearchForm;
use App\Entity\Bibliotheque;
use App\Entity\Subscription;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    

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
}
