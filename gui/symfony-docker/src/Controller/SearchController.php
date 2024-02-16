<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function search(): Response
    {
        return $this->render('search/search.html.twig', [
            'controller_name' => 'SearchController',
        ]);
    }
}
