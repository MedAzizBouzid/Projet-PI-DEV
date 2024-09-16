<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    private $userRepository;
    // public function __construct(UserRepository $userRepository)
    // {
    //     $this->userRepository=$userRepository;
    // }
// /**
//      * Does the authenticator support the given Request?
//      *
//      * If this returns true, authenticate() will be called. If false, the authenticator will be skipped.
//      *
//      * Returning null means authenticate() can be called lazily when accessing the token storage.
//      */
//     public function supports(Request $request): ?bool
//     {
//         return $request->attributes->get('_route')==='app_login'
//         && $request->isMethod('POST');
//     }

//     /**
//      * Create a passport for the current request.
//      *
//      * The passport contains the user, credentials and any additional information
//      * that has to be checked by the Symfony Security system. For example, a login
//      * form authenticator will probably return a passport containing the user, the
//      * presented password and the CSRF token value.
//      *
//      * You may throw any AuthenticationException in this method in case of error (e.g.
//      * a UserNotFoundException when the user cannot be found).
//      *
//      * @throws AuthenticationException
//      */
//     public function authenticate(Request $request): Passport
//     {
//         $user = new User();
//        $user=$this->userRepository->findOneByEmail($request->request->get('email'));
        
//        if(!$user){
//         throw new CustomUserMessageAuthenticationException('Invalid credentials!');
//        }else if($user->getEtat()==0){
//         $this->$this->addFlash(
//            'danger',
//            'your account is banned'
//         );
//        }

//        return new Passport(
//         new UserBadge($request->request->get('email')),
//         new PasswordCredentials($request->request->get('password', '')),
//         [
            
//             new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
//         ]
//     );
//     }

   

//     /**
//      * Called when authentication executed and was successful!
//      *
//      * This should return the Response sent back to the user, like a
//      * RedirectResponse to the last page they visited.
//      *
//      * If you return null, the current request will continue, and the user
//      * will be authenticated. This makes sense, for example, with an API.
//      */
//     public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
//     {
//         dd('succ');

//     }

//     /**
//      * Called when authentication executed, but failed (e.g. wrong username password).
//      *
//      * This should return the Response sent back to the user, like a
//      * RedirectResponse to the login page or a 403 response.
//      *
//      * If you return null, the request will continue, but the user will
//      * not be authenticated. This is probably not what you want to do.
//      */
//     public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
//     {
//         dd($exception);
//     }


    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator,UserRepository $userRepository)
    {
        $this->userRepository=$userRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);
        $user = new User();
               $user=$this->userRepository->findOneByEmail($request->request->get('email'));
                
               if(!$user){
                throw new CustomUserMessageAuthenticationException('Invalid credentials!');
               }else if($user->getEtat()!=0 && $user->getRoles()[0]!="ROLE_ADMIN"){
                throw new CustomUserMessageAuthenticationException('This account is banned!');

              
               }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new RememberMeBadge(),
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }
    

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        return new RedirectResponse($this->urlGenerator->generate('app_Front_index'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
