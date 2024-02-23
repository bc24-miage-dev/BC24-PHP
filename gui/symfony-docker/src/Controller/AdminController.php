<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Resource;
use App\Form\ResourceType;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Report;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
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

    #[Route('/admin/add', name: 'app_admin_add')]
    public function add(Request $request, ManagerRegistry $doctrine): Response
    {   
        if(!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles()))
        {
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
        
        }
        else {
            return $this->render('admin/add.html.twig', [
                'state' => 'fail',
                'form' => $form->createView(),
            ]);
        }
    }

    #[Route('/admin/modify', name: 'app_admin_modify')]
    public function modify(): Response
    {
        if(!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles()))
        {
           return $this->redirectToRoute('app_index');
        }

        return $this->render('admin/modify.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/checkReport', name: 'app_check_report')]
    public function checkReport(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Report::class);
        $reportC = $repository->findallReportedRessource();
        return $this->render('admin/recentReport.html.twig', ['report' => $reportC]);
    }
}
