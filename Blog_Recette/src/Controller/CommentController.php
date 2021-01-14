<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment", name="comment")
     */
    public function index(): Response
    {
        return $this->render('recipe/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

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
     * @Route("{id}/deleteComment", name="comment_delete")
     */
    public function deleteComment(Comment $comment, EntityManagerInterface $manager){
        
        
        if($this->getUser()->isAdmin() || $this->getUser() == $comment->getUser()){
            $recipeId = $comment->getRecipe()->getId();
            $manager->remove($comment);
            $manager->flush();

            return $this->redirectToRoute('detailRecipe', ['id' => $recipeId]);
            
        }
        return $this->redirectToRoute('recipe_index');
    }
}
