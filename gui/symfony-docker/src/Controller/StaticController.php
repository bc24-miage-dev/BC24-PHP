<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Resource;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class StaticController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('static/index.html.twig', [
            'controller_name' => 'StaticController',
        ]);
    }

    #[Route('/about', name: 'app_static_about')]
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
    public function recentReport(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Resource::class);
        $resourcesC = $repository->findLastContaminatedResources();
        return $this->render('static/recent.html.twig', ['resourcesC' => $resourcesC]);
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

    #[Route('/logoutProcess', name: 'app_logoutProcess')]
    public function logoutProcess(): RedirectResponse
    {   
        $this->addFlash('success', 'Vous êtes déconnecté(e) !');
        return $this->redirectToRoute('app_index');
    }
}
