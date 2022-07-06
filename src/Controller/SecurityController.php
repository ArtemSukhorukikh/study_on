<?php

namespace App\Controller;

use App\DTO\UserRegisterDto;
use App\Form\RegisterType;
use App\Exception\BillingUnavailableException;
use App\Security\UserCustomAuthenticator;
use App\Service\BillingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    public BillingClient $billingClient;

    public function __construct(BillingClient $billingClient)
    {
        $this->billingClient = $billingClient;
    }
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('app_home_page');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/register', name: 'app_register')]
    public function register(
        Request $request,
        UserAuthenticatorInterface $userAuthenticationInterface,
        BillingClient $billingClient,
        UserCustomAuthenticator $userCustomAuthenticator,
    ) : Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        $userRegisterDto = new UserRegisterDto();
        $form = $this->createForm(RegisterType::class, $userRegisterDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() &&  $form->isValid()) {
            try {
                $user = $this->billingClient->register($userRegisterDto);
            } catch (BillingUnavailableException $e) {
                return $this->render('security/registration.html.twig', [
                    'form' => $form->createView(),
                    'errors' => json_decode($e->getMessage(), true),
                ]);
            }
            return $userAuthenticationInterface->authenticateUser($user, $userCustomAuthenticator, $request);
        }
        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
            'errors' => ''
        ]);
    }
}
