<?php

namespace App\Controller;

use App\Repository\ResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnsafeResourcesController extends AbstractController
{
    #[Route('/recent', name: 'app_recent')]
    public function recentReport(ResourceRepository $resourceRepository): Response
    {
        $resourcesC = $resourceRepository->findBy(['isContamined' => true], ['date' => 'DESC'], 10);
        $productsC = [];
        foreach ($resourcesC as $resource){
            if ($resource->getResourceName()->getResourceCategory()->getCategory() == 'PRODUIT'){
                $productsC[] = $resource;
            }
        }
        return $this->render('static/recent.html.twig', ['resourcesC' => $productsC]);
    }
}
