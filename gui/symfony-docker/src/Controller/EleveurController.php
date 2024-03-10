<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Form\EleveurBirthType;
use App\Form\ResourceType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/eleveur')]
class EleveurController extends AbstractController
{
    #[Route('/', name: 'app_eleveur_index')]
    public function index(): Response
    {
        return $this->render('pro/eleveur/index.html.twig');
    }

    #[Route('/naissance', name: 'app_eleveur_naissance')]
    public function naissance(Request $request, ManagerRegistry $doctrine): Response
    {
        $resource = new Resource();
        $resource->setIsFinalProduct(false);
        $resource->setIsContamined(false);
        $resource->setPrice(0);
        $resource->setDescription('');
        $resource->setOrigin($this->getUser()->getProductionSite());
        $resource->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $resource->setCurrentOwner($this->getUser());
        $resource->setIsLifeCycleOver(false);
        $form = $this->createForm(EleveurBirthType::class, $resource);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'La naissance de votre animal a bien été enregistrée !');
            return $this->redirectToRoute('app_eleveur_index');
        }

        return $this->render('pro/eleveur/naissance.html.twig', [
            'form' => $form->createView(),
        ]);

    }
}
