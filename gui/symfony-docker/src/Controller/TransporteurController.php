<?php

namespace App\Controller;

use App\Handlers\OwnershipHandler;
use App\Handlers\TransactionHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ResourceOwnerChangerType;
use Symfony\Component\HttpFoundation\Request;

#[Route('/pro/transporteur')]
class TransporteurController extends AbstractController
{

    private TransactionHandler $transactionHandler;

    public function __construct(TransactionHandler $transactionHandler)
    {
        $this->transactionHandler = $transactionHandler;
    }

    #[Route('/', name: 'app_transporteur_index')]
    public function index(): Response
    {
        return $this->render('pro/transporteur/index.html.twig');
    }


    #[Route('/acquisition', name: 'app_transporteur_acquire')]
    public function acquisition(Request $request,
                                ResourceRepository $resourceRepo,
                                OwnershipAcquisitionRequestRepository $ownershipRepo,
                                EntityManagerInterface $entityManager,
                                OwnershipHandler $ownershipHandler): Response
    {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $resource = $resourceRepo->find($form->getData()->getId());
            if (!$resource || $resource->getCurrentOwner()->getWalletAddress() == $this->getUser()->getWalletAddress()) {
                $this->addFlash('error', 'Vous ne pouvez pas demander la propriété de cette ressource');
                return $this->redirectToRoute('app_transporteur_acquire');
            }
            if ($ownershipRepo->findOneBy(['requester' => $this->getUser(), 'resource' => $resource, 'state' => 'En attente'])) {
                $this->addFlash('error', 'Vous avez déjà demandé la propriété de cette ressource');
                return $this->redirectToRoute('app_transporteur_acquire');
            }
            $ownershipHandler->ownershipRequestCreate($this->getUser(), $entityManager, $resource);
            $this->addFlash('success', 'La demande de propriété a bien été envoyée');
            return $this->redirectToRoute('app_transporteur_acquire');
        }
        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/transporteur/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests
        ]);
    }

    #[Route('/list', name: 'app_transporteur_list')]
    public function list(ResourceRepository $resourceRepo,
                         Request $request) : Response
    {
        if ($request->isMethod('POST')) {
            $NFC = $request->request->get('NFC');
            $resources = $resourceRepo->findByWalletAddressAndNFC($this->getUser()->getWalletAddress(),$NFC);
            if($resources == null){
                $this->addFlash('error', 'Cette ressoure ne vous appartient pas');
                return $this->redirectToRoute('app_transporteur_list');
            }
        }
        else{
        $resources = $resourceRepo->findByWalletAddress($this->getUser()->getWalletAddress());
        }

        return $this->render('pro/transporteur/list.html.twig',
            ['resources' => $resources]
        );
    }

    #[Route('/specific/{id}', name: 'app_transporteur_specific')]
    public function specific(ResourceRepository $resourceRepository,
                             $id): Response
    {
        $resource = $resourceRepository->find($id);
        if (!$resource || $this->getUser()->getWalletAddress() != $resource->getCurrentOwner()->getWalletAddress()){
            $this->addFlash('error', 'Cette ressource ne vous appartient pas');
            return $this->redirectToRoute('app_transporteur_list');
        }

        return $this->render('pro/transporteur/specific.html.twig', [
            'resource' => $resource
        ]);
    }

    #[Route('/transaction', name: 'app_transporteur_transferList')]
    public function transferList(OwnershipAcquisitionRequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser() ,'state' => 'En attente']);
        $pastTransactions = $requestRepository->findPastRequests($this->getUser());
        return $this->render('pro/transporteur/transferList.html.twig',
            ['requests' => $requests, 'pastTransactions' => $pastTransactions]
        );
    }

    #[Route('/transaction/{id}', name: 'app_transporteur_transfer', requirements: ['id' => '\d+'])]
    public function transfer($id): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptTransaction($id, $this->getUser());
            $this->addFlash('success', 'Transaction effectuée');
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_transporteur_transferList');
        }
    }

    #[Route('/transactionRefused/{id}', name: 'app_transporteur_transferRefused', requirements: ['id' => '\d+'])]
    public function transferRefused($id): RedirectResponse
    {
        try {
            $this->transactionHandler->refuseTransaction($id, $this->getUser());
            $this->addFlash('success', 'Transaction refusée avec succès');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_transporteur_transferList');
        }
    }

    #[Route('/transaction/all' , name: 'app_transporteur_transferAll')]
    public function transferAll(): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptAllTransactions($this->getUser());
            $this->addFlash('success', 'Toutes les transactions ont été effectuées');
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_transporteur_transferList');
        }
    }
}
