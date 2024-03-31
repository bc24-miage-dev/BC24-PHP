<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\ResourceName;
use App\Form\EquarrisseurAnimalAbattageFormType;
use App\Form\ResourceOwnerChangerType;
use App\Handlers\proAcquireHandler;
use App\Handlers\ResourceHandler;
use App\Repository\ResourceNameRepository;
use App\Repository\ResourceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/equarrisseur')]
class EquarrisseurController extends AbstractController
{
    #[Route('/', name: 'app_equarrisseur_index')]
    public function index(): Response
    {
        return $this->render('pro/equarrisseur/index.html.twig');
    }

    #[Route('/acquisition', name: 'app_equarrisseur_acquire')]
    public function acquisition(Request $request,
                                ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $proAcquireHandler = new proAcquireHandler();

            if($proAcquireHandler->acquireStrict($form, $doctrine, $this->getUser(), 'ANIMAL')){
                $this->addFlash('success', 'L\'animal a bien été enregistré');
            } else {
                $this->addFlash('error', 'Ce tag NFC ne correspond pas à un animal');
            }
            return $this->redirectToRoute('app_equarrisseur_acquire');
        }
        return $this->render('pro/equarrisseur/acquire.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/list/{category}', name: 'app_equarrisseur_list')] // An 'Equarrisseur' have access to the list of his animals and carcasses
    public function list(ResourceRepository $resourceRepo,
                         String $category) : Response
    {
        $resources = $resourceRepo->findByOwnerAndResourceCategory($this->getUser(), strtoupper($category));
        return $this->render('pro/equarrisseur/list.html.twig',
            ['resources' => $resources ]
        );
    }

    #[Route('/specific/{id}', name: 'app_equarrisseur_job')]
    public function job(ResourceRepository $resourceRepo,
                        $id): Response
    {
        $resource = $resourceRepo->findOneBy(['id' => $id, 'currentOwner' => $this->getUser()]);
        if (!$resource) {
            $this->addFlash('error', 'Ressource introuvable');
            return $this->redirectToRoute('app_equarrisseur_list');
        }
        if ($resource->getResourceName()->getResourceCategory()->getCategory() == 'ANIMAL'){
            return $this->render('pro/equarrisseur/job.html.twig', [
                'resource' => $resource,
                'category' => 'ANIMAL'
            ]);
        } else {
            return $this->render('pro/equarrisseur/job.html.twig', [
                'resource' => $resource,
                'category' => 'CARCASSE'
            ]);
        }
    }

    #[Route('/equarrir/{id}', name: 'app_equarrisseur_equarrir')]
    public function equarrir(ManagerRegistry $doctrine,
                             ResourceRepository $resourceRepo,
                             ResourceNameRepository $resourceNameRepo,
                             Request $request,
                             $id)
    {
        $resource = $resourceRepo->findOneBy(['id' => $id, 'currentOwner' => $this->getUser()]);
        if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL'){
            $this->addFlash('error', 'Il y a eu un problème, veuillez contacter un administrateur');
            return $this->redirectToRoute('app_equarrisseur_list');
        }

        $handler = new ResourceHandler();
        $newCarcasse = $handler->createChildResource($resource, $this->getUser());
        $form = $this->createForm(EquarrisseurAnimalAbattageFormType::class, $newCarcasse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $rN = $resourceNameRepo->findByCategoryAndFamily('CARCASSE', $resource->getResourceName()->getFamily()->getName());
            $newCarcasse->setResourceName($rN[0]);
            $resource->setIsLifeCycleOver(true);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($resource);
            $entityManager->persist($newCarcasse);
            $entityManager->flush();
            $this->addFlash('success', 'L\'animal a bien été abattu, une carcasse a été créée');
            return $this->redirectToRoute('app_equarrisseur_index');
        }
        return $this->render('pro/equarrisseur/equarrir.html.twig', [
            'form' => $form->createView()
        ]);

    }

    #[Route('/decoupe/{id}', name: 'app_equarrisseur_decoupe')]
    public function decoupe(ManagerRegistry $doctrine,
                            Request $request,
                            ResourceRepository $resourceRepo,
                            ResourceNameRepository $resourceNameRepo,
                            $id) : Response
    {
        $resource = $resourceRepo->findOneBy(['id'=> $id, 'currentOwner' => $this->getUser()]);
        if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != 'CARCASSE'){
            $this->addFlash('error', 'Il y a eu un problème, veuillez contacter un administrateur');
            return $this->redirectToRoute('app_equarrisseur_list');
        }

        $demiCarcasse = $resourceNameRepo->findByCategoryAndFamily('DEMI-CARCASSE', $resource->getResourceName()->getFamily()->getName())[0];

        $handler = new ResourceHandler();
        $newHalfCarcasse = $handler->createChildResource($resource, $this->getUser());
        $newHalfCarcasse->setResourceName($demiCarcasse);
        $newHalfCarcasse2 = $handler->createChildResource($resource, $this->getUser());
        $newHalfCarcasse2->setResourceName($demiCarcasse);

        //Classic form because two different entities must be processed at once
        if ($request->isMethod('POST')) {
            $newHalfCarcasse->setId($request->request->get('tag1'));
            $newHalfCarcasse2->setId($request->request->get('tag2'));
            $newHalfCarcasse->setWeight($request->request->get('weight1'));
            $newHalfCarcasse2->setWeight($request->request->get('weight2'));
            $resource->setIsLifeCycleOver(true);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($newHalfCarcasse);
            $entityManager->persist($newHalfCarcasse2);
            $entityManager->flush();

            $this->addFlash('success', 'Cette carcasse a bien été découpée');
            return $this->redirectToRoute('app_equarrisseur_index');
        }
        return $this->render('pro/equarrisseur/slice.html.twig');
    }
}
