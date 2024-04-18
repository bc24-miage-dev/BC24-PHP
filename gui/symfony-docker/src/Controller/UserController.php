<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRoleRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use App\Entity\ProductionSite;
use App\Form\ProductionSiteType;

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
    public function deleteUserProcess(EntityManagerInterface$entityManager): RedirectResponse
    {
        $user = $this->getUser();
        if ($user) {
            $user->setDeletedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $entityManager->persist($user);
            $entityManager->flush();
            //Kill la session
            $this->tokenStorage->setToken(null);
            $this->addFlash('success', 'Votre compte a bien été supprimé');
            return $this->redirectToRoute('app_index');
        }
        return $this->redirectToRoute('app_index');
    }

    #[Route('/update', name: 'app_user_update')]
    public function modifUser(Request $request,
                              EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($user) {
            $form = $this->createForm(ModifierUserType::class, $user);
            $form->handleRequest($request);

            if($form -> isSubmitted() && $form -> isValid()){
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('app_user_account');
            }
            $form = $this->createForm(ModifierUserType::class, $user);

            return $this->render('user/ModifAccount.html.twig', ['form' => $form->createView()]);
        }
        return $this->redirectToRoute('app_index');
    }

    #[Route('/request', name: 'app_admin_user_request')]
    public function userRequestRole(Request $request,
                                    EntityManagerInterface $entityManager,
                                    UserRoleRequestRepository $requestRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $UserRoleRequest = new UserRoleRequest();
        $repoRequest = $requestRepository->findBy(['User' => $this->getUser()]);

        if (count($repoRequest) > 0) {
            $UserRoleRequest = $repoRequest[0];
        }
        $form = $this->createForm(UserRoleRequestType::class, $UserRoleRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $UserRoleRequest = $form->getData();
            $UserRoleRequest->setUser($this->getUser());
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

    #[Route('/productionSiteRequest', name: 'app_user_productionSiteRequest')]

    public function createProductionSite(EntityManagerInterface $entityManager,
                                         Request $request): Response
    {
        $productionSite = new ProductionSite();
        $form = $this->createForm(ProductionSiteType::class, $productionSite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productionSite->setValidate(false);
            $entityManager->persist($productionSite);
            $entityManager->flush();

            $this->addFlash('success', 'Demande de création de site de production enregistrée');
            return $this->redirectToRoute('app_admin_user_request');
        }

        return $this->render('user/productionSite.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
