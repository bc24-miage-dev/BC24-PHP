<?php

namespace App\Controller;

use App\Handlers\UserRoleRequestHandler;
use App\Repository\UserRoleRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Form\ModifierUserType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\UserRoleRequest;
use App\Form\UserRoleRequestType;
use App\Entity\ProductionSite;
use App\Form\ProductionSiteType;

#[Route('/user')]
class UserController extends AbstractController
{
    private TokenStorageInterface $tokenStorage;

    private EntityManagerInterface $entityManager;
    private UserRoleRequestHandler $userRoleRequestHandler;
    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager,
                                UserRoleRequestHandler $userRoleRequestHandler)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->userRoleRequestHandler = $userRoleRequestHandler;
    }


    #[Route('/', name: 'app_user_account')]
    public function myAccount(): Response
    {
        return $this->render('user/MyAccount.html.twig');
    }

    #[Route('/deleteAccount', name: 'app_user_delete')]
    public function deleteUser(): Response
    {
        $user = $this->getUser();
        if ($user) {
            return $this->render('user/SuppSoon.html.twig');
        }
        return $this->redirectToRoute('app_index');
    }

    #[Route('/delete', name: 'app_user_delete_process')]
    public function deleteUserProcess(): RedirectResponse
    {
        try{
            $this->userRoleRequestHandler->deleteUser($this->getUser());
            //Kill la session
            $this->tokenStorage->setToken(null);
            $this->addFlash('success', 'Votre compte a bien été supprimé');
            return $this->redirectToRoute('app_index');
        } catch (\Exception $e){
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/update', name: 'app_user_update')]
    public function modifUser(Request $request): Response
    {
        if ($user = $this->getUser()) {
            $form = $this->createForm(ModifierUserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_user_account');
            }
            $form = $this->createForm(ModifierUserType::class, $user);

            return $this->render('user/ModifAccount.html.twig', ['form' => $form->createView()]);
        }
        return $this->redirectToRoute('app_index');
    }

    #[Route('/request', name: 'app_admin_user_request')]
    public function userRequestRole(Request $request,
                                    UserRoleRequestRepository $requestRepository,
                                    UserRoleRequestHandler $requestHandler): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $repoRequest = $requestRepository->findOneBy(['User' => $this->getUser()]);
        $repoRequest ? $UserRoleRequest = $repoRequest : $UserRoleRequest = new UserRoleRequest();
        $form = $this->createForm(UserRoleRequestType::class, $UserRoleRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $requestHandler->initializeRoleRequest($form->getData(), $this->getUser());
            $this->addFlash('success', 'Votre demande à bien été envoyée');
            return $this->redirectToRoute('app_index');
        }
        return $this->render('user/UserRequest.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/productionSiteRequest', name: 'app_user_productionSiteRequest')]

    public function createProductionSite(Request $request): Response
    {
        $form = $this->createForm(ProductionSiteType::class, $productionSite = new ProductionSite());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productionSite->setValidate(false);
            $this->entityManager->persist($productionSite);
            $this->entityManager->flush();

            $this->addFlash('success', 'Demande de création de site de production enregistrée');
            return $this->redirectToRoute('app_admin_user_request');
        }

        return $this->render('user/productionSite.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
