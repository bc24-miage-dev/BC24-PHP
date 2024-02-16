<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->render('account/login.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('account/register.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }
}
