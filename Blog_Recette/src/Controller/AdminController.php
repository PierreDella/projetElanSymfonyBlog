<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** 
* @Route("/admin")   
*/
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/home", name="adminHome")
     */
    public function dashboard(){
        $users = $this->getDoctrine()->getRepository(User::class)->getAllOrder();
        return $this->render('admin/adminHome.html.twig', [
            "users" => $users,
            ]);
    
    }
}
