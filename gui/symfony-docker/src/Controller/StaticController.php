<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaticController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('static/index.html.twig', [
            'controller_name' => 'StaticController',
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('static/about.html.twig', [
            'controller_name' => 'StaticController',
        ]);
    }

    #[Route('/siteInfo', name: 'app_info')]
    public function info(): Response
    {
        return $this->render('static/info.html.twig', [
            'controller_name' => 'StaticController',
        ]);
    }

    #[Route('/recent', name: 'app_recent')]
    public function recentReport(): Response
    {
        return $this->render('static/recent.html.twig', [
            'controller_name' => 'StaticController',
        ]);
    }
}
