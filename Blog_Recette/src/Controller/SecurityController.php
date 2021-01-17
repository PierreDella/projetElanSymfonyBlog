<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\RegistrationFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, SluggerInterface $slugger): Response
    {   
        if($this->getUser()){ //On vérifie si l'utilisateur est connecté
            $this->addFlash("error", "Tu es deja connecté");
            return $this->redirectToRoute('home');
        }
        $user = new User();//Si utilisateur pas connecté on fait un nouvel obejt user
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { // Si le formulaire est valide
            
             //On recupere les images 
             /** @var UploadedFile $picture */
            $picture = $form->get('picture')->getData();

            if($picture){
                $originalPicture = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);

                // On genere un nouveau nom de fichier
                // On en fait une part de l'URL
                $safePicture = $slugger->slug($originalPicture);
                $newPicture = $safePicture.'-'.uniqid().'.'.$picture->guessExtension();

                try {
                    //On copie le fichier dans le dossier uploads
                    $picture->move(
                        $this->getParameter('pictures_directory'),
                        $newPicture
                    );
                } catch (FileException $e){
                    //Permet de récuperer l'upload en cas d'erreur
                }
                
                $user->setPicture($newPicture);
            }
            
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user); 
            $entityManager->flush();
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/user/{id}/changePassword", name="user_edit_password")
     */
    public function editUserPassword(User $user = null, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response {

        if ($user){
            if($user->getId() == $this->getUser()->getId()){

                $form = $this->createForm(ChangePasswordType::class);

                $form->handleRequest($request);
        
                if($form->isSubmitted() && $form->isValid()) {
        
                    $mdp = $form->get('plainPassword')->getData();
                    $mdp = $passwordEncoder->encodePassword($user, $mdp);
                    $this->getDoctrine()->getRepository(User::class)->upgradePassword($user, $mdp);    
                    $this->addFlash("success", "Le mot de passe a bien été changé.");

                    return $this->redirectToRoute("user_show", ['id'=>$user->getId()]);          
                }
        
                return $this->render("security/editPassword.html.twig", [
                    "formUser" => $form->createView()
                ]);

            } else {
                $this->addFlash("error", "Accès interdit.");
                return $this->redirectToRoute("home");
            }
        }else{
            $this->addFlash("error", "Cet administrateur n'existe pas.");
            return $this->redirectToRoute('home');
        }
    }
}
