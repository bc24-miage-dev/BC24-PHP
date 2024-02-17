<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function register(PersistenceManagerRegistry $doctrine, Request $request): Response
    {

        $entityManager = $doctrine->getManager();
        //User
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        //Traitement de la requête
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_login');
        }

        return $this->render('account/register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
