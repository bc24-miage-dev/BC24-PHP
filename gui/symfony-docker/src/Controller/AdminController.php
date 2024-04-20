<?php

namespace App\Controller;

use App\Entity\ProductionSite;
use App\Form\ProductionSiteType;
use App\Form\ResourceModifierType;
use App\Form\ResourceType;
use App\Handlers\ResourceHandler;
use App\Repository\ProductionSiteRepository;
use App\Repository\ReportRepository;
use App\Repository\ResourceRepository;
use App\Repository\UserRepository;
use App\Repository\UserRoleRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_index')]
    public function admin(): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/add', name: 'app_admin_add')] // Resource creation
    public function add(Request $request,
                        EntityManagerInterface $entityManager,
                        ResourceHandler $handler): Response
    {
        $resource = $handler->createDefaultNewResource($this->getUser());
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($resource);
            $entityManager->flush();

            $this->addFlash('success', 'Ressource ajoutée avec succès');
            return $this->render('admin/admin.html.twig');

        } else {
            return $this->render('admin/add.html.twig', [
                'form' => $form->createView(),
            ]);
        }
    }

    #[Route('/modify', name: 'app_admin_modify')] // Resource list for modification
    public function modify(ResourceRepository $resourceRepo): Response
    {
        $resources = $resourceRepo->findAll();
        return $this->render('admin/modify.html.twig', ['resources' => $resources]);
    }

    #[Route('/modify/{id}', name: 'app_admin_modifySpecific')] // Resource modification
    public function modifySpecific(EntityManagerInterface $entityManager,
                                   Request $request,
                                   ResourceHandler $resourceHandler,
                                   ResourceRepository $resourceRepo,
                                   $id): Response
    {
        $resource = $resourceRepo->find($id);
        if (!$resource) {
            $this->addFlash('error', 'Ressource introuvable');
            return $this->redirectToRoute('app_admin_modify');
        }
        $form = $this->createForm(ResourceModifierType::class, $resource);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $resource->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            if ($form->get('isContamined')->getData()) {
                $resourceHandler->contaminateChildren($entityManager, $resource);
            }
            $entityManager->persist($resource);
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_modify');
        }
        return $this->render('admin/modifySpecific.html.twig',
            [   'form' => $form->createView(),
                'resource' => $resource,
                'composants' => $resource->getComponents()]);
    }

    #[Route('/reportList', name: 'app_admin_reportList')]
    public function reportList(ReportRepository $reportRepo): Response
    {
        $report = $reportRepo->findBy(criteria:['read' => false], orderBy:['date' => 'DESC']);
        return $this->render('admin/reportList.html.twig', ['report' => $report]);
    }

    #[Route('/checkReport/{id}', name: 'app_admin_checkReport')]

    public function checkReport(ReportRepository $reportRepo,
                                $id): Response
    {
        $report = $reportRepo->find($id);
        $resource = $report->getResource();
        return $this->render('admin/checkReport.html.twig', ['report' => $report, 'resource' => $resource]);
    }

    #[Route('/checkReportProcess/{idRep}/{action}', name: 'app_admin_checkReportProcess')]
    public function checkReportProcess(ReportRepository $reportRepo,
                                        EntityManagerInterface $entityManager,
                                       $idRep, $action): RedirectResponse
    {
        $report = $reportRepo->find($idRep);
        $resource = $report->getResource();
        if ($action == 'delete') {
            $resource->setIsContamined(true);
        }
        $report->setRead(true);

        $entityManager->persist($resource);
        $entityManager->persist($report);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_reportList');
    }

    #[Route('/userList', name: 'app_admin_userList')]

    public function userList(UserRepository $userRepo,
                             ProductionSiteRepository $productionSiteRepo): Response
    {
        $users = $userRepo->findAll();
        $pSites = $productionSiteRepo->findAll();
        return $this->render('admin/userList.html.twig', ['users' => $users, 'pSites' => $pSites]);
    }

    #[Route('/userEdit/{id}/{role}', name: 'app_admin_userEdit')]
    public function userEdit(UserRepository $userRepo,
                             EntityManagerInterface $entityManager,
                             $id, $role): RedirectResponse
    {
        $user = $userRepo->find($id);
        $user->setSpecificRole("$role");
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_userList');
    }

    #[Route('/userProdSiteEdit/{id}/{productionSiteId}', name: 'app_admin_userProdSiteEdit')]
    public function userProdSiteEdit(UserRepository $userRepo,
                                     ProductionSiteRepository $productionSiteRepo,
                                     EntityManagerInterface $entityManager,
                                     $id, $productionSiteId) : RedirectResponse
    {
        $user = $userRepo->find($id);
        if ($productionSiteId != -1) {
            $productionSite = $productionSiteRepo->find($productionSiteId);
        }
        else {
            $productionSite = null;
        }
        $user->setProductionSite($productionSite);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_userList');
    }

    #[Route('/productionSite', name: 'app_productionSite')]

    public function createProductionSite(EntityManagerInterface $entityManager,
                                         Request $request): Response
    {
        $productionSite = new ProductionSite();
        $form = $this->createForm(ProductionSiteType::class, $productionSite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productionSite->setValidate(true); // Admin-created production sites are automatically validated
            $entityManager->persist($productionSite);
            $entityManager->flush();

            $this->addFlash('success', 'Site de production créé avec succès');
            return $this->redirectToRoute('app_admin_index');
        }
        return $this->render('admin/productionSite.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/request/check', name: 'app_admin_request_check')]
    public function userRequestCheck(UserRoleRequestRepository $roleRequestRepo): Response
    {
        $UserRoleRequest = $roleRequestRepo->findBy(['Read' => false]); // Select all unread requests
        return $this->render('admin/requestList.html.twig', ['UserRoleRequest' => $UserRoleRequest]);
    }

    #[Route('/request/roleEdit/{id}/{validation}/{role}', name: 'app_admin_request_roleEdit')]
    public function userRequestRoleEdit(EntityManagerInterface $entityManager,
                                        UserRoleRequestRepository $roleRequestRepo,
                                        ProductionSiteRepository $productionSiteRepo,
                                        UserRepository $userRepo,
                                        $id, $validation, $role): Response
    {
        $userRoleRequest = $roleRequestRepo->find($id);

        if ($validation == "true") {
            $user = $userRepo->find($userRoleRequest->getUser());
            $user->setWalletAddress($userRoleRequest->getWalletAddress());
            $entityManager->persist($user->setSpecificRole("$role"));
            $user->setProductionSite($productionSiteRepo->findOneBy(["id" => $userRoleRequest->getProductionSite()]));
        }
        $userRoleRequest->setRead(true);
        $entityManager->persist($userRoleRequest);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_userList');
    }

    #[Route('/request/roleEdit/WalletAdress/{id}', name: 'app_admin_userWalletAddressEdit')]
    public function userWalletAddressEdit(EntityManagerInterface $entityManager,
                                          UserRepository $userRepo,
                                          Request $request,
                                          $id,): Response
    {
        if($request->isMethod('POST')) {
        $walletAddress = $request->request->get('walletAddress');
        $user = $userRepo->find($id);
        $user->setWalletAddress($walletAddress);
        $entityManager->persist($user);
        $entityManager->flush();
        }
        else{
            $this->addFlash('error', 'Erreur lors de la modification de l\'adresse de portefeuille');
        }
        return $this->redirectToRoute('app_admin_userList');
    }


    #[Route('/request/productionSiteRequest', name: 'app_admin_request_productionSiteRequest')]
    public function usineRequest(UserRoleRequestRepository $roleRequestRepo): Response
    {
        $productionSite = $roleRequestRepo->findBy(['Read' => false]);
        return $this->render('admin/productionSiteRequestList.html.twig', ['productionSiteList' => $productionSite]);
    }

    #[Route('/request/productionSiteRequestEdit/{id}/{validation}', name: 'app_admin_request_productionSiteRequestEdit')]

    public function usineRequestEdit(EntityManagerInterface $entityManager,
                                     UserRoleRequestRepository $roleRequestRepo,
                                     ProductionSiteRepository $productionSiteRepo,
                                     $id, $validation): Response
    {
        $userRoleRequest = $roleRequestRepo->find($id);
        $productionSite = $productionSiteRepo->find($userRoleRequest->getProductionSite());
        if ($validation == "true") {
            $productionSite->setValidate(true);
        }
        else {
            $userRoleRequest->setRead(true);
        }
        $entityManager->persist($userRoleRequest);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_request_productionSiteRequest');
    }


}
