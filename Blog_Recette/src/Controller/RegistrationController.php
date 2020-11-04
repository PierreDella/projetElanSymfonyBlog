<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, SluggerInterface $slugger): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
             //On recupere les images 
             /** @var UploadedFile $picture */
            $picture = $form->get('picture')->getData();
            

            if($picture){
                $originalPicture = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);

                $safePicture = $slugger->slug($originalPicture);
                $newPicture = $safePicture.'-'.uniqid().'.'.$picture->guessExtension();

                try {
                    $picture->move(
                        $this->getParameter('pictures_directory'),
                        $newPicture
                    );
                } catch (FileException $e){
                    //Mise en place des exceptions a faire plus tard
                }
                
                $user->setPicture($newPicture);
            }
            // //On genere un nouveau nom de fichier
            // $fichier = md5(uniqid()). '.' .$pictures->guessExtensions();
            
            // //On copie le fichier dans le dossier uploads
            // $picture->move(
            //     $this->getParameter('pictures_directory'),
            //     $fichier
            
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setPicture($picture);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
