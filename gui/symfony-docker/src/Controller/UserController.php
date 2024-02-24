<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Form\ModifierUserType;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    private $tokenStorage;
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    
    
    #[Route('/user', name: 'app_user_account')]
    public function myAccount(): Response
    {
        return $this->render('user/MyAccount.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/postDelete', name: 'app_user_postDelete')]
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

    #[Route('/user/delete', name: 'app_user_delete')]
    public function deleteUser2(User $user = null, ManagerRegistry $doctrine): RedirectResponse
    {   
        $user = $this->getUser();
        if ($user) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            //Kill la session
            $this->tokenStorage->setToken(null);
            return $this->redirectToRoute('app_index');
        }
        return $this->render('user/CompteSupprime.html.twig', [
            'information' => 'Compte inexsitant',
        ]);
    }

    #[Route('/user/update', name: 'app_user_update')]
    public function modifUser(Request $request, ManagerRegistry $doctrine): Response
    {   
        $user = $this->getUser();
        if ($user) {
            $form = $this->createForm(ModifierUserType::class, $user);
            $form->handleRequest($request);

            if($form -> isSubmitted() && $form -> isValid()){
                
                $entityManager = $doctrine->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('app_user_account');
            }
            $form = $this->createForm(ModifierUserType::class, $user);

            return $this->render('user/ModifAccount.html.twig', ['form' => $form->createView()
        ]);
        }
        return $this->render('user/CompteSupprime.html.twig', [
            'information' => 'Compte inexistant',
        ]);
    }


}
