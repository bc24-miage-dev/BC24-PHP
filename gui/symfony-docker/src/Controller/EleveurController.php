<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Form\EleveurBirthType;
use App\Form\ResourceOwnerChangerType;
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

    #[Route('/list', name: 'app_eleveur_list')]
    public function list(ManagerRegistry $doctrine) : Response
    {
        $this->denyAccessUnlessGranted( attribute: 'ROLE_ELEVEUR');
        $repository = $doctrine->getRepository(Resource::class);
        $animaux = $repository->findBy(['currentOwner' => $this->getUser()]);
        return $this->render('pro/eleveur/list.html.twig',
            ['animaux' => $animaux]
        );
    }

    #[Route('/specific/{id}', name: 'app_eleveur_edit')]
    public function edit(Resource $resource, Request $request, ManagerRegistry $doctrine): Response
    {
        //TODO : THIS FUNCTION IS GIBBERISH MADE BY COPILOT
        $this->denyAccessUnlessGranted( attribute: 'ROLE_ELEVEUR');
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'Les informations de votre animal ont bien été modifiées !');
            return $this->redirectToRoute('app_eleveur_list');
        }
        return $this->render('pro/eleveur/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/arrivage', name: 'app_eleveur_acquire')]
    public function acquisition(Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $id = $data->getId();

            $resource = $doctrine->getRepository(Resource::class)->find($id);
            if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL') {
                $this->addFlash('error', 'Ce tag NFC ne correspond pas à un animal');
                return $this->redirectToRoute('app_eleveur_acquire');
            }

            $resource->setCurrentOwner($this->getUser());
            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'L\'animal a bien été enregistré dans votre élevage');
            return $this->redirectToRoute('app_eleveur_acquire');
        }
        return $this->render('pro/eleveur/acquire.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
