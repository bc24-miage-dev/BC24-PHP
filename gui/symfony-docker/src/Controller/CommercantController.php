<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/commercant')]
class CommercantController extends AbstractController
{
    #[Route('/', name: 'app_commercant_index')]
    public function index(): Response
    {
        if (! ($this->getUser() && $this->getUser()->getRoles() && in_array('ROLE_COMMERCANT', $this->getUser()->getRoles()))){
            return $this->redirectToRoute('app_index');
        }
        return $this->render('pro/commercant/index.html.twig');
    }
}
