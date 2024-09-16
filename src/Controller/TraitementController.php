<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TraitementController extends AbstractController
{
    #[Route('/home', name: 'app_Front_index')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'TraitementController',
        ]);
    }
    #[Route('/random', name: 'app_random')]
    public function random(): Response
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $longueurMax = strlen($caracteres);
        $chaineAleatoire = '';
        for ($i = 0; $i < 6; $i++)
        {
        $chaineAleatoire .= $caracteres[rand(0, $longueurMax - 1)];
        }
       
        return $this->render('front/random.html.twig', [
            'random' => $chaineAleatoire,
        ]);
    }
    #[Route('/administration', name: 'app_back_index')]
    public function indexB(): Response
    {
        return $this->render('back/index.html.twig', [
            'controller_name' => 'TraitementController',
        ]);
    }
    #[Route('/home', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('front/index.html.twig', [
            
        ]);
    }
    #[Route('/404', name: 'app_err')]
    public function err(): Response
    {
        return $this->render('front/404.html.twig', [
            
        ]);
    }
  
    #[Route('/profile/{id}', name: 'app_profile')]
    public function profile(User $user): Response
    {
        return $this->render('front/profileUser.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/Edit/{id}', name: 'app_profileEdit')]
    public function profileEdit(Request $request, User $user, UserRepository $userRepository,SluggerInterface $slugger): Response
    {$form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image->move(
                        $this->getParameter('user_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'image$photoname' property to store the PDF file name
                // instead of its contents
                $user->setImage($newFilename);
            }
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_profile', ['id'=>$user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/editUser.html.twig', [
            'user' => $user,
            'form' => $form, 
        ]);
    }
    #[Route('/{id}', name: 'app_userD', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
    }
    
}
