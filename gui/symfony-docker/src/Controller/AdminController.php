<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Resource;
use App\Form\ResourceType;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Report;
use App\Form\ResourceModifierType;
use App\Entity\User;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function admin(): Response
    {

        if(!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles()))
        {
           return $this->redirectToRoute('app_index');

        }

        return $this->render('admin/admin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/add', name: 'app_admin_add')]
    public function add(Request $request, ManagerRegistry $doctrine): Response
    {
        if (!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_index');

        }
        $resource = new Resource();
        $resource->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();

            $entityManager->persist($resource);
            $entityManager->flush();

            return $this->render('admin/add.html.twig', [
                'state' => 'success',
            ]);

        } else {
            return $this->render('admin/add.html.twig', [
                'state' => 'fail',
                'form' => $form->createView(),
            ]);
        }
    }

    #[Route('/modify', name: 'app_admin_modify')]
    public function modify(ManagerRegistry $doctrine): Response
    {
        if (!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_index');
        }
        $resources = $doctrine->getRepository(Resource::class)->findAll();
        return $this->render('admin/modify.html.twig', ['resources' => $resources]);
    }

    #[Route('/modify/{id}', name: 'app_admin_modifySpecific')]
    public function modifySpecific(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        if (!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_index');
        }
        $resource = $doctrine->getRepository(Resource::class)->find($id);
        $resource->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $form = $this->createForm(ResourceModifierType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_modify');
        }
        return $this->render('admin/modifySpecific.html.twig', ['form' => $form->createView(), 'resource' => $resource]);
    }

    #[Route('/reportList', name: 'app_admin_reportList')]
    public function reportList(ManagerRegistry $doctrine): Response
    {
        if (!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_index');
        }
        $repository = $doctrine->getRepository(Report::class);
        $report = $repository->findallReportedRessource();
        return $this->render('admin/reportList.html.twig', ['report' => $report]);
    }

    #[Route('/checkReport/{id}', name: 'app_admin_checkReport')]

    public function checkReport(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        if (!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_index');
        }

        $report = $doctrine->getRepository(Report::class)->find($id);
        $resource = $report->getResource();


        if ($request->isMethod('POST')) {
            if ($request->request->has('contaminate')) {
                $resource->setIsContamined(true);
                $report->setRead(true);
            } elseif ($request->request->has('mark_safe')) {
                $report->setRead(true);
            }


            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->persist($report);
            $entityManager->flush();


            return $this->redirectToRoute('app_admin_reportList');
        }


        return $this->render('admin/checkReport.html.twig', ['report' => $report, 'resource' => $resource]);


    }
    #[Route('/userList', name: 'app_admin_userList')]

    public function userList(ManagerRegistry $doctrine): Response
    {
        if (!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_index');
        }
        $repository = $doctrine->getRepository(User::class);
        $users = $repository->findAll();
        return $this->render('admin/userList.html.twig', ['users' => $users]);
    }


    #[Route('/userEdit/{id}', name: 'app_admin_userEdit')]
    public function userEdit(ManagerRegistry $doctrine, $id) : Response

    {
        if (!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_index');
        }
        $user = $doctrine->getRepository(User::class)->find($id);
        $roles = $user->getRoles();
        array_push($roles, "ROLE_ADMIN");
        $user->setRoles($roles);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_userList');
    }
}
