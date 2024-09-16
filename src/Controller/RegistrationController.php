<?php

namespace App\Controller;

use App\Entity\ForgetPwd;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\ForgetPwdRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\LoginAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Twilio\Rest\Client;

class RegistrationController extends AbstractController
{
    
    public function sendSmsAction()
    {
        $TWILIO_ACCOUNT_SID= 'AC41806a149ebbbac29e16f2d14d8a5e64';
        $TWILIO_AUTH_TOKEN= 'd9e9f4901e96a2e3851bb30f3837d3d3';
        $TWILIO_NUMBER= '+19036648231';
        $client = new Client($TWILIO_ACCOUNT_SID,$TWILIO_AUTH_TOKEN);
        $client->messages->create(
            '+21692703351', // numéro de téléphone du destinataire
            array(
                'from' => $TWILIO_NUMBER,
                'body' => 'Your Account has been verified'
            )
        );

        return new Response('SMS envoyé !');
    }
    public function random(): string
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $longueurMax = strlen($caracteres);
        $chaineAleatoire = '';
        for ($i = 0; $i < 6; $i++)
        {
        $chaineAleatoire .= $caracteres[rand(0, $longueurMax - 1)];
        }
       
        return  $chaineAleatoire;
        
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, 
    UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator,
     EntityManagerInterface $entityManager,SluggerInterface $slugger,SendMailService $mail,JWTService $jwt): Response
    {
        $err="";
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->setData(['captcha'=>'test']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
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
            if($request->get('captcha')==$request->get('captchaVerif'))
            {

                $entityManager->persist($user);
                $entityManager->flush();
    
                $header=[
                    'type'=>'JWT',
                    'alg'=>'HS256'
                ];
    
               $payload=[
                'user_id'=> $user->getId()
               ];
    
               $token=$jwt->generate($header,$payload,$this->getParameter('app.jwtsecret'));
               
               
                // do anything else you need here, like send an email
                $mail->send(
                    'pidevsymfony8@gmail.com',
                    $user->getEmail(),
                    'Activation',
                    'email',
                    [
                        'user'=>$user,
                        'token'=>$token
                    ]
                );
    
                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );
            }else{
                $err="captcha invalid";
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'random'=>$this->random(),
            'err'=>$err
        ]);
    }

    #[Route('/verify/{token}', name: 'app_verify_email')]
    public function verifyUser($token,JWTService $jwt,UserRepository $userRepository, EntityManagerInterface $em): Response
    {
       
       if( $jwt->isValid($token)&& !$jwt->isExpired($token)&& $jwt->check($token,$this->getParameter('app.jwtsecret'))){
        $payload=$jwt->getPayload($token);
        $user=$userRepository->find($payload['user_id']);
        if($user && !$user->isVerified()){
            $user->setIsVerified(true);
            $em->flush($user);
            // $this->sendSmsAction();
            $this->addFlash('success', 'Your email address has been verified.');
            return $this->redirectToRoute('app_Front_index');

        }
       }
       $this->addFlash('danger','token invalid');

        return $this->redirectToRoute('app_Front_index');
    }
    #[Route('/forgetPwdl', name: 'app_forgetPwd')]
    public function forgetPwd(Request $request): Response
    {
       

        return $this->render('front/forgetPwd.html.twig',[
            
        ]);
    }

    #[Route('/send', name: 'app_send')]
    public function send(Request $request ,UserRepository $userRepository,  ForgetPwdRepository $forgetPwdRepository,SendMailService $mail): Response
    {
        $code=$this->random();
        $user = new User();
        $user=$userRepository->findOneByEmail($request->get('email')) ;
       if($user){
        $forgetPwd=new ForgetPwd();
        $forgetPwd->setCode($code);
        $forgetPwd->setUser($user);
        $forgetPwdRepository->save($forgetPwd,true);
        $mail->send(
            'pidevsymfony8@gmail.com',
            $user->getEmail(),
            'Reset your password',
            'forgetPwdTemplate',
            [
                'user'=>$user,
                'code'=>$code
            ]
        );

       }

        return $this->render('front/index.html.twig',[
            
        ]);
    }
    #[Route('/resretPwd', name: 'app_resretPwd')]
    public function resretPwd(Request $request ,UserRepository $userRepository,  ForgetPwdRepository $forgetPwdRepository,UserPasswordHasherInterface $userPasswordHasher): Response
    {
       
        $error="";
        if($request->isMethod('post')){
           
        // if($request->get('submit')==""){

            $user = new User();
            $user=$userRepository->findOneByEmail($request->get('email')) ;
            $forgetPwd=new ForgetPwd();
            $forgetPwd=$forgetPwdRepository->findOneByUser($user);
            // dd($forgetPwd);
            if($forgetPwd && $request->get('code')==$forgetPwd->getCode()){
                
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $request->get('newPwd')
                    )
                );
                $userRepository->save($user,true);
                $forgetPwdRepository->remove($forgetPwd,true);
                return $this->render('front/index.html.twig',[
                    
                ]);
            }else{
                $error="Invalid code or email";
            }
            // dd($error);
         }
        return $this->render('security/resetPwd.html.twig',[
            'error'=>$error
        ]);

    }
}
