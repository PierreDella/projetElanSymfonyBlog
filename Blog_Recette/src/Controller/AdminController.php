<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AdminController extends AbstractController
{
    /**
     * @Route("/users", name="user_index")
     */
    public function indexUsersAdmin()
    {   
        if($this->getUser()->isAdmin()){
            $repo = $this->getDoctrine()->getRepository(User::class);
            $users = $repo->findAll();
            return $this->render('user/index.html.twig', [
                'users' => $users,
            ]);
        } else {
            $this->addFlash("error", "Accès interdit.");
            return $this->redirectToRoute("home");
        }   
    }
        
    /**
     * @Route("/admin/", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/adminHome", name="adminHome")
     */
    public function dashboardAdmin(){
        if($this->getUser()->isAdmin()){
            $users = $this->getDoctrine()->getRepository(User::class)->getAllOrder();
            return $this->render('admin/adminHome.html.twig', [
                "users" => $users,
            ]);

        } else {
            $this->addFlash("error", "Accès interdit.");
            return $this->redirectToRoute("home");
        }
    }
   
}
