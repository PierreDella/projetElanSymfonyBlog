<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Recipe;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="user_index")
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $users = $repo->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/user/{id}", name="user_show", methods="GET")
     */
    public function show(User $user = null)
    { //Ce fait grace au @ParamConverter

        // On veut recuperer la liste des user et la donner a twig
        // On veut un repository dans lequel on interragi avec doctrine, où l'on veut un repository qui gere user
        //On rajoute App/Entity/Repository
        // $repository = $this->getDoctrine()->getRepository(User::class);
        // Ensuite on lui dit sous quelle forme on veut les infos
        // $user = $repository->find($id);

        //Cpdt les dependances permettent de ce contenter de ca avec @ParamConverter

        if ($user) {
            return $this->render('user/show.html.twig', [
                'user' => $user
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/user/delete/{id}", name="user_delete")
     */
    public function deleteUser(User $user, EntityManagerInterface $manager)
    {
        $manager->remove($user);
        $manager->flush();

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/user/{id}/edit", name="user_edit")
     */
    public function editUser(User $user = null, Request $request, EntityManagerInterface $manager): Response
    {

        if ($user) {
            if ($user->getId() == $this->getUser()->getId()) {
                $form = $this->createForm(UserType::class, $user);

                $form->handleRequest($request);


                if ($form->isSubmitted() && $form->isValid()) {

                    dd($form['picture']->getData());
                    $manager->persist($user);
                    $manager->flush();
                    return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
                }

                return $this->render("user/edit.html.twig", [
                    "formUser" => $form->createView(),
                    "user" => $user

                ]);
            } else {

                return $this->redirectToRoute('home');
            }
        } else {
            return $this->redirectToRoute('home');
        }
    }
}
