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

    #[Route('/consommateur', name: 'app_consommateur')]
    public function consommateur(): Response
    {
        return $this->render('static/consommateur.html.twig', [
            'controller_name' => 'StaticController',
        ]);
    }

    #[Route('/producteur', name: 'app_producteur')]
    public function producteur(): Response
    {
        return $this->render('static/producteur.html.twig', [
            'controller_name' => 'StaticController',
        ]);
    }

    #[Route('/usine', name: 'app_usine')]
    public function usine(): Response
    {
        return $this->render('static/usine.html.twig', [
            'controller_name' => 'StaticController',
        ]);
    }

    #[Route('/equarisseur', name: 'app_equarisseur')]
    public function equarisseur(): Response
    {
        return $this->render('static/equarisseur.html.twig', [
            'controller_name' => 'StaticController',
        ]);
    }
}
