<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\ResourceName;
use App\Form\EquarrisseurAnimalAbattageFormType;
use App\Form\ResourceOwnerChangerType;
use App\Handlers\OwnershipHandler;
use App\Handlers\proAcquireHandler;
use App\Handlers\ResourceHandler;
use App\Handlers\TransactionHandler;
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

    private TransactionHandler $transactionHandler;

    public function __construct(TransactionHandler $transactionHandler)
    {
        $this->transactionHandler = $transactionHandler;
    }

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
            if ($ownershipRepo->findOneBy(['requester' => $this->getUser(), 'resource' => $resource, 'state' => 'En attente'])){
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
        return $this->render('pro/equarrisseur/list.html.twig',
            ['resources' => $resources,]
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
        $category = $resource->getResourceName()->getResourceCategory()->getCategory();
            return $this->render('pro/equarrisseur/job.html.twig', [
                'resource' => $resource,
                'category' => $category
            ]);
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
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser() ,'state' => 'En attente']);
        $pastTransactions = $requestRepository->findPastRequests($this->getUser());
        return $this->render('pro/equarrisseur/transferList.html.twig',
            ['requests' => $requests, 'pastTransactions' => $pastTransactions]
        );
    }

    #[Route('/transaction/{id}', name: 'app_equarrisseur_transfer', requirements: ['id' => '\d+'])]
    public function transfer($id): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptTransaction($id, $this->getUser());
            $this->addFlash('success', 'Transaction effectuée');
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_equarrisseur_transferList');
        }
    }

    #[Route('/transactionRefused/{id}', name: 'app_equarrisseur_transferRefused', requirements: ['id' => '\d+'])]
    public function transferRefused($id): RedirectResponse
    {
        try {
            $this->transactionHandler->refuseTransaction($id, $this->getUser());
            $this->addFlash('success', 'Transaction refusée avec succès');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_equarrisseur_transferList');
        }
    }

    #[Route('/transaction/all' , name: 'app_equarrisseur_transferAll')]
    public function transferAll(): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptAllTransactions($this->getUser());
            $this->addFlash('success', 'Toutes les transactions ont été effectuées');
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_equarrisseur_transferList');
        }
    }
}
