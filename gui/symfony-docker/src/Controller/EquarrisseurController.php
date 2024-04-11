<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\ResourceName;
use App\Form\EquarrisseurAnimalAbattageFormType;
use App\Form\ResourceOwnerChangerType;
use App\Handlers\OwnershipHandler;
use App\Handlers\proAcquireHandler;
use App\Handlers\ResourceHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ResourceNameRepository;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
                                ResourceRepository $resourceRepo,
                                OwnershipAcquisitionRequestRepository $ownershipRepo,
                                OwnershipHandler $ownershipHandler,
                                EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resource =$resourceRepo->find($form->getData()->getId());
            if (!$resource || $resource->getCurrentOwner()->getWalletAddress() == $this->getUser()->getWalletAddress()) {
                $this->addFlash('error', 'Vous ne pouvez pas demander la propriété de cette ressource');
                return $this->redirectToRoute('app_equarrisseur_acquire');
            }
            if ($ownershipRepo->findOneBy(['requester' => $this->getUser(), 'resource' => $resource, 'validated' => false])){
                $this->addFlash('error', 'Vous avez déjà demandé la propriété de cette ressource');
                return $this->redirectToRoute('app_equarrisseur_acquire');
            }

            $ownershipHandler->ownershipRequestCreate($this->getUser(), $entityManager, $resource);
            $this->addFlash('success', 'La demande de propriété a bien été envoyée');
            return $this->redirectToRoute('app_equarrisseur_acquire');
        }

        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/equarrisseur/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests
        ]);
    }

    #[Route('/list/{category}', name: 'app_equarrisseur_list')] // An 'Equarrisseur' have access to the list of his animals and carcasses
    public function list(ResourceRepository $resourceRepo,
                         String $category,
                         Request $request) : Response
    {
        if ($request->isMethod('POST')) {
            $NFC = $request->request->get('NFC');
            $resources = $resourceRepo->findByWalletAddressNFC($this->getUser()->getWalletAddress(),$NFC);
            if($resources == null){
                $this->addFlash('error', 'Cette ressoure ne vous appartient pas');
                return $this->redirectToRoute('app_equarrisseur_list', ['category' => $category]);
            }
        }
        else{
        $resources = $resourceRepo->findByWalletAddressCategory($this->getUser()->getWalletAddress(),$category);
        }

        // $resources = $resourceRepo->findByOwnerAndResourceCategory($this->getUser(), strtoupper($category));
        return $this->render('pro/equarrisseur/list.html.twig',
            ['resources' => $resources ]
        );
    }

    #[Route('/specific/{id}', name: 'app_equarrisseur_job')]
    public function job(ResourceRepository $resourceRepo,
                        $id): Response
    {

        $resource = $resourceRepo->findOneBy(['id' => $id]);

        if (!$resource ||
            $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress()){
            $this->addFlash('error', 'Ressource introuvable');
            return $this->redirectToRoute('app_equarrisseur_index');
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
    public function equarrir(EntityManagerInterface $entityManager,
                             ResourceRepository $resourceRepo,
                             ResourceNameRepository $resourceNameRepo,
                             Request $request,
                             $id)
    {
        $resource = $resourceRepo->findOneBy(['id' => $id]);
        if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != 'ANIMAL'
            || $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress())
        {
            $this->addFlash('error', 'Il y a eu un problème, veuillez contacter un administrateur');
            return $this->redirectToRoute('app_equarrisseur_list', ['category' => 'ANIMAL']);
        }

        $handler = new ResourceHandler();
        $newCarcasse = $handler->createChildResource($resource, $this->getUser());
        $form = $this->createForm(EquarrisseurAnimalAbattageFormType::class, $newCarcasse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $rN = $resourceNameRepo->findOneByCategoryAndFamily('CARCASSE', $resource->getResourceName()->getResourceFamilies()[0]->getName());
            // Since the $resource is an animal, we can assume that it has only one family (vache, porc, etc.)
            $newCarcasse->setResourceName($rN);
            $resource->setIsLifeCycleOver(true);
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
    public function decoupe(EntityManagerInterface $entityManager,
                            Request $request,
                            ResourceRepository $resourceRepo,
                            ResourceNameRepository $resourceNameRepo,
                            ResourceHandler $handler,
                            $id) : Response
    {
        $resource = $resourceRepo->findOneBy(['id'=> $id]);
        if (!$resource || $resource->getResourceName()->getResourceCategory()->getCategory() != 'CARCASSE'
        || $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress())
        {
            $this->addFlash('error', 'Il y a eu un problème, veuillez contacter un administrateur');
            return $this->redirectToRoute('app_equarrisseur_list', ['category' => 'CARCASSE']);
        }

        $demiCarcasse = $resourceNameRepo->findOneByCategoryAndFamily('DEMI-CARCASSE', $resource->getResourceName()->getResourceFamilies()[0]->getName());
        //Same here, we can assume that a carcasse has only one family

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

            $entityManager->persist($newHalfCarcasse);
            $entityManager->persist($newHalfCarcasse2);
            $entityManager->flush();

            $this->addFlash('success', 'Cette carcasse a bien été découpée');
            return $this->redirectToRoute('app_equarrisseur_index');
        }
        return $this->render('pro/equarrisseur/slice.html.twig');
    }

    #[Route('/transaction', name: 'app_equarrisseur_transferList')]
    public function transferList(OwnershipAcquisitionRequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser() ,'validated' => false]);
        return $this->render('pro/equarrisseur/transferList.html.twig',
            ['requests' => $requests]
        );
    }

    #[Route('/transaction/{id}', name: 'app_equarrisseur_transfer', requirements: ['id' => '\d+'])]
    public function transfer($id,
                             OwnershipAcquisitionRequestRepository $requestRepository,
                             EntityManagerInterface $entityManager ): RedirectResponse
    {
        $request = $requestRepository->find($id);
        if (!$request || $request->getInitialOwner() != $this->getUser()){
            $this->addFlash('error', 'Erreur lors de la transaction');
            return $this->redirectToRoute('app_equarrisseur_transferList');
        }
        $resource = $request->getResource();
        $resource->setCurrentOwner($request->getRequester());
        $request->setValidated(true);
        $entityManager->persist($resource);
        $entityManager->persist($request);
        $entityManager->flush();
        $this->addFlash('success', 'Transaction effectuée');

        return $this->redirectToRoute('app_equarrisseur_transferList');
    }

    #[Route('/transaction/all' , name: 'app_equarrisseur_transferAll')]
    public function transferAll(OwnershipAcquisitionRequestRepository $requestRepository,
                                EntityManagerInterface $entityManager): RedirectResponse
    {
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser() ,'validated' => false]);
        if (!$requests){
            $this->addFlash('error', 'Il n\'y a pas de transaction à effectuer');
            return $this->redirectToRoute('app_equarrisseur_transferList');
        }
        foreach ($requests as $request){
            $resource = $request->getResource();
            $resource->setCurrentOwner($request->getRequester());
            $request->setValidated(true);
            $entityManager->persist($resource);
            $entityManager->persist($request);
        }
        $entityManager->flush();
        $this->addFlash('success', 'Toutes les transactions ont été effectuées');

        return $this->redirectToRoute('app_equarrisseur_transferList');
    }
}
