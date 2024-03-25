<?php

namespace App\Controller;

use App\Entity\ProductionSite;
use App\Entity\Report;
use App\Entity\Resource;
use App\Entity\User;
use App\Entity\UserRoleRequest;
use App\Form\ProductionSiteType;
use App\Form\ResourceModifierType;
use App\Form\ResourceType;
use App\Handlers\ResourceHandler;
use Doctrine\Persistence\ManagerRegistry;
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

    #[Route('/add', name: 'app_admin_add')]
    public function add(Request $request, ManagerRegistry $doctrine): Response
    {
        $resource = new Resource();
        $resource->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $resource->setIsLifeCycleOver(false);
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

    #[Route('/modify', name: 'app_admin_modify')]
    public function modify(ManagerRegistry $doctrine): Response
    {
        $resources = $doctrine->getRepository(Resource::class)->findAll();
        return $this->render('admin/modify.html.twig', ['resources' => $resources]);
    }

    #[Route('/modify/{id}', name: 'app_admin_modifySpecific')]
    public function modifySpecific(ManagerRegistry $doctrine, Request $request, ResourceHandler $resourceHandler, $id): Response
    {
        $resource = $doctrine->getRepository(Resource::class)->find($id);
        $resource->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $components = $resource->getComponents();
        $form = $this->createForm(ResourceModifierType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();

            if ($form->get('isContamined')->getData()) {
                $resourceHandler->contaminateChildren($entityManager, $resource);
            }
            $entityManager->persist($resource);
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_modify');
        }
        return $this->render('admin/modifySpecific.html.twig', ['form' => $form->createView(), 'resource' => $resource, 'composants' => $components]);
    }

    #[Route('/reportList', name: 'app_admin_reportList')]
    public function reportList(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Report::class);
        $report = $repository->findallReportedRessource();
        return $this->render('admin/reportList.html.twig', ['report' => $report]);
    }

    #[Route('/checkReport/{id}', name: 'app_admin_checkReport')]

    public function checkReport(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $report = $doctrine->getRepository(Report::class)->find($id);
        $resource = $report->getResource();

        return $this->render('admin/checkReport.html.twig', ['report' => $report, 'resource' => $resource]);

    }

    #[Route('/checkReportProcess/{idRep}/{action}', name: 'app_admin_checkReportProcess')]
    public function checkReportProcess(Request $request, ManagerRegistry $doctrine, $idRep, $action): RedirectResponse
    {
        $report = $doctrine->getRepository(Report::class)->find($idRep);
        $resource = $report->getResource();
        if ($action == 'delete') {
            $resource->setIsContamined(true);
        }
        $report->setRead(true);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($resource);
        $entityManager->persist($report);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_reportList');
    }

    #[Route('/userList', name: 'app_admin_userList')]

    public function userList(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(User::class);
        $users = $repository->findAll();
        return $this->render('admin/userList.html.twig', ['users' => $users]);
    }

    #[Route('/userEdit/{id}/{role}', name: 'app_admin_userEdit')]
    public function userEdit(ManagerRegistry $doctrine, $id, $role): Response
    {
        

        $user = $doctrine->getRepository(User::class)->find($id);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($user->setSpecificRole("$role"));
        $entityManager->flush();
        
        return $this->redirectToRoute('app_admin_userList');
    }

    #[Route('/productionSite', name: 'app_productionSite')]

    public function createProductionSite(ManagerRegistry $doctrine, Request $request): Response
    {
        $productionSite = new ProductionSite();
        $form = $this->createForm(ProductionSiteType::class, $productionSite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $productionSite->setValidate(true);
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
    public function userRequestCheck(Request $request, ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(UserRoleRequest::class);
        $UserRoleRequest = $repository->findBy(['Read' => false]);
        return $this->render('admin/requestList.html.twig', ['UserRoleRequest' => $UserRoleRequest]);
    }

    #[Route('/request/roleEdit/{id}/{validation}/{role}', name: 'app_admin_request_roleEdit')]
    public function userRequestRoleEdit(ManagerRegistry $doctrine, $id, $validation, $role): Response
    {
        $userRoleRequestRepository = $doctrine->getRepository(UserRoleRequest::class)->find($id);
        $userRoleRequest = $userRoleRequestRepository;
        if ($validation == "true") {
            $user = $doctrine->getRepository(User::class)->find($userRoleRequest->getUser());
            $entityManager = $doctrine->getManager();
            $entityManager->persist($user->setSpecificRole("$role"));
            $user->setProductionSite($doctrine->getRepository(ProductionSite::class)->findOneBy(["id" => $userRoleRequest->getProductionSite()]));
        }

        $userRoleRequest->setRead(true);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($userRoleRequest);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_userList');
    }

    #[Route('/request/productionSiteRequest', name: 'app_admin_request_productionSiteRequest')]
    public function usineRequest(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(UserRoleRequest::class);
        $productionSite = $repository->findBy(['Read' => false]);
        return $this->render('admin/productionSiteRequestList.html.twig', ['productionSiteList' => $productionSite]);
    }

    #[Route('/request/productionSiteRequestEdit/{id}/{validation}', name: 'app_admin_request_productionSiteRequestEdit')]

    public function usineRequestEdit(ManagerRegistry $doctrine, $id, $validation): Response
    {
        $userRoleRequest = $doctrine->getRepository(UserRoleRequest::class)->find($id);
        $productionSite = $doctrine->getRepository(ProductionSite::class)->find($userRoleRequest->getProductionSite());
        if ($validation == "true") {
            $productionSite->setValidate(true);
        }
        else {
            $userRoleRequest->setRead(true);
        }
        $entityManager = $doctrine->getManager();
        $entityManager->persist($userRoleRequest);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_request_productionSiteRequest');
    }


}
