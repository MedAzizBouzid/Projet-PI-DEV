<?php
namespace App\Controller;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Facebook;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginAuthenticator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\HttpFoundation\Request;

class FacebookController extends AbstractController
{
   
    private $provider;

    private $github_provider;
 
 
    public function __construct()
    {
       $this->provider=new Facebook([
         'clientId'          => $_ENV['FCB_ID'],
         'clientSecret'      => $_ENV['FCB_SECRET'],
         'redirectUri'       => $_ENV['FCB_CALLBACK'],
         'graphApiVersion'   => 'v15.0',
     ]);
 
 
    //  $this->github_provider=new Github([
    //      'clientId'          => $_ENV['GITHUB_ID'],
    //      'clientSecret'      => $_ENV['GITHUB_SECRET'],
    //      'redirectUri'       => $_ENV['GITHUB_CALLBACK'],
    //  ]);
 
 
    }
    // #[Route('/home', name: 'app_home')]
    // public function index(): Response
    // {
    //     return $this->render('home/index.html.twig', [
    //         'controller_name' => 'HomeController',
    //     ]);
    // }
 
     #[Route('/', name: 'app_home')]
     public function index(): Response
     {
         return $this->render('home/index.html.twig');
     }
 
     #[Route('/fcb-login', name: 'fcb_login')]
     public function fcbLogin(): Response
     {
          
         $helper_url=$this->provider->getAuthorizationUrl();
 
         return $this->redirect($helper_url);
     }
 
 
     #[Route('/fcb-callback', name: 'fcb_callback' ,methods: [ 'GET','POST'])]
     public function fcbCallBack(UserRepository $userRepository,UserAuthenticatorInterface $userAuthenticator,LoginAuthenticator $authenticator
     ,Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
     {
        $token=$this->provider->getAccessToken('authorization_code',[
            'code'=>$_GET['code']
        ]);
        $data=$this->provider->getResourceOwner($token)->toArray();

        $email=$data['email'];
        $nom=$data['first_name'];
        $prenom=$data['last_name'];
        $username=$data['first_name'];
        $image=$data['picture_url'];
        $user_exist=$userRepository->findOneByEmail($email);
        if($user_exist){
            return $userAuthenticator->authenticateUser(
                $user_exist,
                $authenticator,
                $request
            );
        }else{
            $user= new User();
            $user->setEmail($email);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setUserName($username);
            $user->setImage($image);
            $user->setNumTel(92703351);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    "Dhia1234"
                )
            );

            $userRepository->save($user,true);
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );

        }

        // dd($data);
        return $this->render('security/login.html.twig');

     }
}