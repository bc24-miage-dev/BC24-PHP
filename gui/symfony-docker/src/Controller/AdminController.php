<?php

namespace App\Controller;

use App\Entity\ProductionSite;
use App\Form\ProductionSiteType;
use App\Form\ResourceModifierType;
use App\Form\ResourceType;
use App\Form\SearchType;
use App\Handlers\ProHandler;
use App\Handlers\ResourceHandler;
use App\Repository\ProductionSiteRepository;
use App\Repository\ReportRepository;
use App\Repository\ResourceRepository;
use App\Repository\UserRepository;
use App\Repository\UserRoleRequestRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\BlockChainService;
use App\Handlers\RoleConversionWithBlockChainHandler;


#[Route('/admin')]
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private BlockChainService $blockChainService;

    public function __construct(EntityManagerInterface $entityManager, BlockChainService $blockChainService)
    {
        $this->entityManager = $entityManager;
        $this->blockChainService = $blockChainService;
    }



    #[Route('/', name: 'app_admin_index')]
    public function admin(): Response
    {
        return $this->render('admin/admin.html.twig');
    }


    #[Route('/add', name: 'app_admin_add')] // Resource creation
    public function add(Request $request,
                        ResourceHandler $handler): Response
    {
        return $this->render('admin/add.html.twig');
    }


    #[Route('/modify', name: 'app_admin_modify')] // Resource list for modification
    public function modify(ResourceRepository $resourceRepo,
                           Request $request): Response
    {
        $resources = $resourceRepo->getFewLastResources();
        return $this->render('admin/modify.html.twig', ['resources' => $resources, 'form' => $form->createView()]);
    }


    #[Route('/modify/{id}', name: 'app_admin_modifySpecific')] // Resource modification
    public function modifySpecific(Request $request,
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
                $resourceHandler->contaminateChildren($resource);
            }
            $this->entityManager->persist($resource);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_admin_modify');
        } else {
            $this->addFlash('error', 'Erreur lors de la modification de la ressource');
        }
        return $this->render('admin/modifySpecific.html.twig',
            [   'form' => $form->createView(),
                'resource' => $resource,
                'composants' => $resource->getComponents()]);
    }


    #[Route('/reportList', name: 'app_admin_reportList')]
    public function reportList(ReportRepository $reportRepo): Response
    {
        $report = $reportRepo->findBy(criteria:['Readed' => false], orderBy:['date' => 'DESC']);
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
                                       $idRep,
                                       $action): RedirectResponse
    {
        $report = $reportRepo->find($idRep);
        $resource = $report->getResource();
        if ($action == 'delete') {
            $resource->setIsContamined(true);
        }
        $report->setReaded(true);

        $this->entityManager->persist($resource);
        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_reportList');
    }


    #[Route('/userList', name: 'app_admin_userList')]

    public function userList(UserRepository $userRepo,
                             ProductionSiteRepository $productionSiteRepo): Response
    {
        $users = $userRepo->getAllActiveUsers();
        $pSites = $productionSiteRepo->findAll();
        return $this->render('admin/userList.html.twig', ['users' => $users, 'pSites' => $pSites]);
    }


    #[Route('/userEdit/{id}/{role}', name: 'app_admin_userEdit')]
    public function userEdit(UserRepository $userRepo,
                             $id, $role,RoleConversionWithBlockChainHandler $roleConversionWithBlockChainHandler): RedirectResponse
    {
        $user = $userRepo->find($id);
        $user->setSpecificRole("$role");
        $role = $roleConversionWithBlockChainHandler->convertRoleToBlockchainRole($role);
        $this->blockChainService->assignRole($user->getWalletAddress(), $role);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_userList');
    }


    #[Route('/userProdSiteEdit/{id}/{productionSiteId}', name: 'app_admin_userProdSiteEdit')]
    public function userProdSiteEdit(UserRepository $userRepo,
                                     ProductionSiteRepository $productionSiteRepo,
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
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_userList');
    }


    #[Route('/productionSite', name: 'app_productionSite')]
    public function createProductionSite(Request $request): Response
    {
        $productionSite = new ProductionSite();
        $form = $this->createForm(ProductionSiteType::class, $productionSite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productionSite->setValidate(true); // Admin-created production sites are automatically validated
            $this->entityManager->persist($productionSite);
            $this->entityManager->flush();

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
        $UserRoleRequest = $roleRequestRepo->findBy(['Readed' => false]); // Select all unread requests
        return $this->render('admin/requestList.html.twig', ['UserRoleRequest' => $UserRoleRequest]);
    }


    #[Route('/request/roleEdit/{id}/{validation}/{role}', name: 'app_admin_request_roleEdit')]
    public function userRequestRoleEdit(UserRoleRequestRepository $roleRequestRepo,
                                        ProductionSiteRepository $productionSiteRepo,
                                        UserRepository $userRepo,
                                        BlockChainService $blockChainService,
                                        RoleConversionWithBlockChainHandler $roleConversionWithBlockChainHandler,
                                        $id, $validation, $role): Response
    {
        $userRoleRequest = $roleRequestRepo->find($id);
        if ($validation == "true") {
            $user = $userRepo->find($userRoleRequest->getUser());
            $this->entityManager->persist($user->setSpecificRole("$role"));
            $user->setProductionSite($productionSiteRepo->findOneBy(["id" => $userRoleRequest->getProductionSite()]));
            $role = $roleConversionWithBlockChainHandler->convertRoleToBlockchainRole($role);
            $blockChainService->assignRole($user->getWalletAddress(), $role);
            $blockChainService->giveETHToWalletAddress($user->getWalletAddress());

        }
        $userRoleRequest->setReaded(true);
        $this->entityManager->persist($userRoleRequest);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_userList');
    }


    #[Route('/request/roleEdit/WalletAdress/{id}', name: 'app_admin_userWalletAddressEdit')]
    public function userWalletAddressEdit(UserRepository $userRepo,
                                          Request $request,
                                          $id): Response
    {
        if($request->isMethod('POST')) {
        $walletAddress = $request->request->get('walletAddress');
        $user = $userRepo->find($id);
        $user->setWalletAddress($walletAddress);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        }
        else{
            $this->addFlash('error', 'Erreur lors de la modification de l\'adresse de portefeuille');
        }
        return $this->redirectToRoute('app_admin_userList');
    }


    #[Route('/request/productionSiteRequest', name: 'app_admin_request_productionSiteRequest')]
    public function usineRequest(UserRoleRequestRepository $roleRequestRepo): Response
    {
        $productionSite = $roleRequestRepo->findBy(['Readed' => false]);
        return $this->render('admin/productionSiteRequestList.html.twig', ['productionSiteList' => $productionSite]);
    }


    #[Route('/request/productionSiteRequestEdit/{id}/{validation}', name: 'app_admin_request_productionSiteRequestEdit')]

    public function usineRequestEdit(UserRoleRequestRepository $roleRequestRepo,
                                     ProductionSiteRepository $productionSiteRepo,
                                     $id, $validation): Response
    {
        $userRoleRequest = $roleRequestRepo->find($id);
        $productionSite = $productionSiteRepo->find($userRoleRequest->getProductionSite());
        if ($validation == "true") {
            $productionSite->setValidate(true);
        }
        else {
            $userRoleRequest->setReaded(true);
        }
        $this->entityManager->persist($userRoleRequest);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_request_productionSiteRequest');
    }
}
