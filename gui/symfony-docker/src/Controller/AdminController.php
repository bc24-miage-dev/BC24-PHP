<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function add(): Response
    {   
        if(!$this->getUser() || !$this->getUser()->getRoles() || !in_array('ROLE_ADMIN', $this->getUser()->getRoles()))
        {
           return $this->redirectToRoute('app_index');
        }
        return $this->render('admin/add.html.twig', [
            'controller_name' => 'AdminController',
        ]);
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
}
