<?php

namespace App\Controller;

use App\Form\SearchType;
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
    public function index(Request $request): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $id = $data->getId();
            return $this->redirect($this->generateUrl('app_search_result', ['id' => $id]));
        }
        return $this->render('static/index.html.twig', [
            'form' => $form->createView()
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
    public function recentReport(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Resource::class);
        $resourcesC = $repository->findBy(['isContamined' => true], ['date' => 'DESC'], 10);
        $productsC = [];
        foreach ($resourcesC as $resource){
            if ($resource->getResourceName()->getResourceCategory()->getCategory() == 'PRODUIT'){
                $productsC[] = $resource;
            }
        }
        return $this->render('static/recent.html.twig', ['resourcesC' => $productsC]);
    }
}
