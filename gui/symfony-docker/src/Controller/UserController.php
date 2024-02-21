<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    private $tokenStorage;
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    #[Route('/MyAccount', name: 'app_myaccount')]
    public function index(): Response
    {
        return $this->render('user/MyAccount.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/MyAccount_suppSoon', name: 'app_deleteMyAccount')]
    public function deleteUser(User $user = null, ManagerRegistry $doctrine): Response
    {   
        $user = $this->getUser();
        if ($user) {
            return $this->render('user/SuppSoon.html.twig',[]);
        }
        return $this->render('user/CompteSupprime.html.twig', [
            'information' => 'Compte inexsitant',
        ]);
    }

    #[Route('/MyAccount_supp', name: 'app_deleteMyAccount2')]
    public function deleteUser2(User $user = null, ManagerRegistry $doctrine): RedirectResponse
    {   
        $user = $this->getUser();
        if ($user) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            $this->tokenStorage->setToken(null);
            return $this->redirectToRoute('app_logout');
        }
        return $this->render('user/CompteSupprime.html.twig', [
            'information' => 'Compte inexsitant',
        ]);
    }


}
