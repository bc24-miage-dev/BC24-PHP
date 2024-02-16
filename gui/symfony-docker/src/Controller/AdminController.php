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
        return $this->render('admin/admin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/add', name: 'app_add')]
    public function add(): Response
    {
        return $this->render('admin/add.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/modify', name: 'app_modify')]
    public function modify(): Response
    {
        return $this->render('admin/modify.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
