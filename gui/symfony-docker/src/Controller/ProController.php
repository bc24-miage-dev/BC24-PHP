<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProController extends AbstractController
{
    #[Route('/pro', name: 'app_pro')]
    public function proRoutage(): RedirectResponse
    {
        if (!$this->isPro()){
            return $this->redirectToRoute('app_index');
        }
        return match ($this->getUser()->getRoles()[1]) {
            'ROLE_ELEVEUR' => $this->redirectToRoute('app_eleveur_index'),
            'ROLE_TRANSPORTEUR' => $this->redirectToRoute('app_transporteur_index'),
            'ROLE_EQUARRISSEUR' => $this->redirectToRoute('app_equarrisseur_index'),
            'ROLE_USINE' => $this->redirectToRoute('app_usine_index'),
            'ROLE_COMMERCANT' => $this->redirectToRoute('app_commercant_index'),
            'ROLE_ADMIN' => $this->redirectToRoute('app_admin_index'),
            //Shouldn't be possible but just to put a default case :
            default => $this->redirectToRoute('app_index'),
        };
    }

    private function isPro(): bool
    {
        $isPro=false;
        if ($this->getUser() && $this->getUser()->getRoles() ){
            $roles = $this->getUser()->getRoles();
            $isPro=in_array('ROLE_ELEVEUR', $roles) || in_array('ROLE_TRANSPORTEUR', $roles)
            || in_array('ROLE_EQUARRISSEUR', $roles) || in_array('ROLE_USINE', $roles)
            || in_array('ROLE_COMMERCANT', $roles) || in_array('ROLE_ADMIN', $roles);
        }
        return $isPro ;
    }
}
