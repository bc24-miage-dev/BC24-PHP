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
use App\Entity\UserRoleRequest;
use App\Form\UserRoleRequestType;

#[Route('/user')]
class UserController extends AbstractController
{
    private $tokenStorage;
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }


    #[Route('/', name: 'app_user_account')]
    public function myAccount(): Response
    {
        return $this->render('user/MyAccount.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/deleteAccount', name: 'app_user_delete')]
    public function deleteUser(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        if ($user) {
            return $this->render('user/SuppSoon.html.twig');
        }
        return $this->redirectToRoute('app_index');
    }

    #[Route('/delete', name: 'app_user_delete_process')]
    public function deleteUserProcess(ManagerRegistry $doctrine): RedirectResponse
    {
        $user = $this->getUser();
        if ($user) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            //Kill la session
            $this->tokenStorage->setToken(null);
            $this->addFlash('success', 'Votre compte a bien été supprimé');
            return $this->redirectToRoute('app_index');
        }
        return $this->redirectToRoute('app_index');
    }

    #[Route('/update', name: 'app_user_update')]
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
        return $this->redirectToRoute('app_index');
    }

    #[Route('/request', name: 'app_admin_user_request')]
    public function userRequestRole(Request $request, ManagerRegistry $doctrine): Response
    {
        $UserRoleRequest = new UserRoleRequest();
        $user = $this->getUser();
        $repository = $doctrine->getRepository(UserRoleRequest::class);
        $repoRequest = $repository->findBy(['User'=>$user]);

        if (count($repoRequest) > 0) {
            $UserRoleRequest = $repoRequest[0];
        }
        $form = $this->createForm(UserRoleRequestType::class, $UserRoleRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $UserRoleRequest->setUser($user);
            $UserRoleRequest->setRead(false);
            $UserRoleRequest->setDateRoleRequest(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $entityManager->persist($UserRoleRequest);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande à bien été envoyée');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('user/UserRequest.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
