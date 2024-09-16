<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use App\Form\RegistrationFormType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin')]
class UserController extends AbstractController
{
    /**************affichage************ */
    #[Route('/', name: 'app_user_admin', methods: ['GET'])]
    public function admin(UserRepository $userRepository, PaginatorInterface $paginator,Request $request): Response
    {
        $users=$userRepository->findAllUser('["ROLE_ADMIN"]');
        $users = $paginator->paginate(
            $users, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            3/*limit per page*/
        );
        return $this->render('back/tableAdmin.html.twig', [
            'users' => $users,
        ]);
    }
    #[Route('/client', name: 'app_user_client', methods: ['GET'])]
    public function client(UserRepository $userRepository, PaginatorInterface $paginator,Request $request): Response
    {
        $users=$userRepository->findAllUser('[]');
        $users = $paginator->paginate(
            $users, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            12/*limit per page*/
        );
        return $this->render('back/tableClient.html.twig', [
            'users' => $users,
        ]);
    }
    #[Route('/coach', name: 'app_user_coach', methods: ['GET'])]
    public function coach(UserRepository $userRepository): Response
    {
        return $this->render('back/tableCoach.html.twig', [
            'users' => $userRepository->findAllUser('["ROLE_COACH"]'),

        ]);
    }
    // /**************ajout************ */

    // #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, UserRepository $userRepository ,RoleRepository $roleRepository): Response
    // {
    //     $user = new User();
    //     $role=$roleRepository->find(2);
    //     $form = $this->createForm(UserType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $user->setRole($role);
    //         $user->setEtat(0);

    //         $userRepository->save($user, true);

    //         return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('front/signup.html.twig', [
    //         'user' => $user,
    //         'form' => $form,
    //     ]);
    // }
    #[Route('/newA', name: 'app_user_newA', methods: ['GET', 'POST'])]
    public function newA(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository ,RoleRepository $roleRepository,SluggerInterface $slugger): Response
    {
        $user = new User();
        $role=[];
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
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

            return $this->redirectToRoute('app_user_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/FormAdmin.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/newC', name: 'app_user_newC', methods: ['GET', 'POST'])]
    public function newC(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository ,RoleRepository $roleRepository,SluggerInterface $slugger): Response
    {
        $user = new User();
        $role=[];
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $roles[] = 'ROLE_COACH';
            $user->setRoles($roles);
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

            return $this->redirectToRoute('app_user_coach', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/FormCoach.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    // /************************** */


    // #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    // public function show(User $user): Response
    // {
    //     return $this->render('user/show.html.twig', [
    //         'user' => $user,    
    //     ]);
    // }

    // /**************modif************ */

    // #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, User $user, UserRepository $userRepository): Response
    // {
    //     $form = $this->createForm(UserType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $userRepository->save($user, true);

    //         return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('user/edit.html.twig', [
    //         'user' => $user,
    //         'form' => $form,
    //     ]);
    // }
    #[Route('/{id}/editA', name: 'app_user_editA', methods: ['GET', 'POST'])]
    public function editA(Request $request, User $user, UserRepository $userRepository,SluggerInterface $slugger): Response
    {
        $form = $this->createForm(RegistrationFormType::class, $user);
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

            return $this->redirectToRoute('app_user_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/FormAdmin.html.twig', [
            'user' => $user,
            'form' => $form, 
        ]);
    }
    #[Route('/{id}/editC', name: 'app_user_editC', methods: ['GET', 'POST'])]
    public function editC(Request $request, User $user, UserRepository $userRepository,SluggerInterface $slugger): Response
    {
        $form = $this->createForm(RegistrationFormType::class, $user);
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

            return $this->redirectToRoute('app_user_coach', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/FormCoach.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    // /**************supp************ */

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_client', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/A/{id}', name: 'app_user_deleteA', methods: ['POST'])]
    public function deleteA(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_admin', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/C/{id}', name: 'app_user_deleteC', methods: ['POST'])]
    public function deleteC(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_coach', [], Response::HTTP_SEE_OTHER);
    }
    /***************************Bann Admin************************* */

    #[Route('/{id}/bann', name: 'app_user_bannA', methods: [ 'POST'])]
    public function bannA(Request $request, User $user, UserRepository $userRepository): Response
    {
    
            $user->setEtat(1);
            $userRepository->save($user, true);
        if($user->getRoles()[0]=="ROLE_ADMIN"){

            return $this->redirectToRoute('app_user_admin', [], Response::HTTP_SEE_OTHER);
        }else if($user->getRoles()[0]=="ROLE_USER"){

            return $this->redirectToRoute('app_user_client', [], Response::HTTP_SEE_OTHER);
        }else{
            return $this->redirectToRoute('app_user_coach', [], Response::HTTP_SEE_OTHER);

        }
    }
    #[Route('/{id}/anbann', name: 'app_user_anbannA', methods: [ 'POST'])]
    public function anbannA(Request $request, User $user, UserRepository $userRepository): Response
    {
    
            
            $user->setEtat(0);
            $userRepository->save($user, true);
            if($user->getRoles()[0]=="ROLE_ADMIN"){

                return $this->redirectToRoute('app_user_admin', [], Response::HTTP_SEE_OTHER);
            }else if($user->getRoles()[0]=="ROLE_USER"){
    
                return $this->redirectToRoute('app_user_client', [], Response::HTTP_SEE_OTHER);
            }else{
                return $this->redirectToRoute('app_user_coach', [], Response::HTTP_SEE_OTHER);
    
            }
    }
    /***************************Bann Coach************************* */

    // #[Route('/{id}/bannC', name: 'app_user_bannC', methods: [ 'POST'])]
    // public function bannC(Request $request, User $user, UserRepository $userRepository): Response
    // {
    
            
    //         $user->setEtat(1);
    //         $userRepository->save($user, true);

    //     return $this->redirectToRoute('app_user_coach', [], Response::HTTP_SEE_OTHER);
    // }
    // #[Route('/{id}/anbannC', name: 'app_user_anbannC', methods: [ 'POST'])]
    // public function anbannC(Request $request, User $user, UserRepository $userRepository): Response
    // {
    
            
    //         $user->setEtat(0);
    //         $userRepository->save($user, true);

    //     return $this->redirectToRoute('app_user_coach', [], Response::HTTP_SEE_OTHER);
    // }
    /***************************Bann user************************* */

    // #[Route('/{id}/bannU', name: 'app_user_bannU', methods: [ 'POST'])]
    // public function bannU(Request $request, User $user, UserRepository $userRepository): Response
    // {
    
            
    //         $user->setEtat(1);
    //         $userRepository->save($user, true);

    //     return $this->redirectToRoute('app_user_coach', [], Response::HTTP_SEE_OTHER);
    // }
    // #[Route('/{id}/anbannU', name: 'app_user_anbannU', methods: [ 'POST'])]
    // public function anbannU(Request $request, User $user, UserRepository $userRepository): Response
    // {
    
            
    //         $user->setEtat(0);
    //         $userRepository->save($user, true);

    //     return $this->redirectToRoute('app_user_coach', [], Response::HTTP_SEE_OTHER);
    // }
    /***************************PDF************************* */
    #[Route('/coachPdf/{id}', name: 'app_user_coachPdf', methods: ['GET'])]
    public function coachpDF(User $user ,UserRepository $userRepository): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $dompdf = new Dompdf($options);
        $date = new DateTime();
        $dateString = $date->format('d-m-Y ');
        $html= $this->renderView('back/listCoachPdf.html.twig', [
            'user' => $user,
            'date'=>$dateString

        ]);
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
        return $this->render('back/tableCoach.html.twig', [
            'users' => $userRepository->findAllUser('["ROLE_COACH"]'),

        ]);
    }
}
