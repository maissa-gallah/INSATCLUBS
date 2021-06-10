<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class SecurityController extends AbstractController
{
    /**
     * @Route("/adduser")
     */
    public function addAction()
    {$userManager= $this->container->get('fos_user.user_manager');
    $user= $userManager->createUser();
    $user->setUsername('newUserBYUM');
    $user->setRoles(array('ROLE_ADMIN'));
    $user->setEmail('newUser@gmail.com');

    }


    /**
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY') and not is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
    }
}
