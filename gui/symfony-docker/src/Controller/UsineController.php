<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/usine')]
class UsineController extends AbstractController
{
    #[Route('/', name: 'app_usine_index')]
    public function index(): Response
    {
        return $this->render('pro/usine/index.html.twig');
    }

    
}
