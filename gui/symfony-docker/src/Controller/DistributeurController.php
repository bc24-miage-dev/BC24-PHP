<?php

namespace App\Controller;

use App\Handlers\OwnershipHandler;
use App\Handlers\proAcquireHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ResourceOwnerChangerType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ResourceNfcType;

#[Route('/pro/distributeur')]
class DistributeurController extends AbstractController
{
    #[Route('/', name: 'app_distributeur_index')]
    public function index(): Response
    {
        return $this->render('pro/distributeur/index.html.twig');
    }

    #[Route('/acquisition', name: 'app_distributeur_acquire')]
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
                return $this->redirectToRoute('app_distributeur_acquire');
            }
            if ($ownershipRepo->findOneBy(['requester' => $this->getUser(), 'resource' => $resource, 'state' => 'En attente'])){
                $this->addFlash('error', 'Vous avez déjà demandé la propriété de cette ressource');
                return $this->redirectToRoute('app_distributeur_acquire');
            }

            $ownershipHandler->ownershipRequestCreate($this->getUser(), $entityManager, $resource);
            $this->addFlash('success', 'La demande de propriété a bien été envoyée');
            return $this->redirectToRoute('app_distributeur_acquire');
        }

        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/distributeur/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests
        ]);
    }

    #[Route('/list', name: 'app_distributeur_list')]
    public function list(ResourceRepository $resourceRepo, Request $request) : Response
    {
        if ($request->isMethod('POST')) {
            $NFC = $request->request->get('NFC');
            $produits = $resourceRepo->findByWalletAddressAndNFC($this->getUser()->getWalletAddress(),$NFC);
            if($produits == null){
                $this->addFlash('error', 'Cette ressoure ne vous appartient pas');
                return $this->redirectToRoute('app_distributeur_list');
            }
        }
        else{
        $produits = $resourceRepo->findByWalletAddress($this->getUser()->getWalletAddress());
        }
        return $this->render('pro/distributeur/list.html.twig',
            ['resources' => $produits]
        );
    }

    #[Route('/specific/{id}', name: 'app_distributeur_specific')]
    public function specific(ResourceRepository $resourceRepo, $id) : Response
    {
        $resource = $resourceRepo->findOneBy(['id' => $id]);
        if (!$resource || $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress()) {
            $this->addFlash('error', 'Aucun produit vous appartenant avec cet id n\'a été trouvé');
            return $this->redirectToRoute('app_distributeur_list');
        }
        return $this->render('pro/distributeur/specific.html.twig',
            ['resource' => $resource]
        );
    }



    #[Route('/vente', name: 'app_distributeur_vendu')]
    public function vendre(Request $request,
                           ResourceRepository $resourceRepo,
                           EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ResourceNfcType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $nfcTag = $form->get('id')->getData();
            $resource = $resourceRepo->findOneBy(['id' => $nfcTag]);

            if (!$resource ||
                $resource->isIsLifeCycleOver() ||
                $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress())
            {
                $this->addFlash('error', 'Aucun produit vous appartenant avec ce tag NFC n\'a été trouvé');
                return $this->redirectToRoute('app_distributeur_vendu');
            }

            $resource->setIsLifeCycleOver(true);
            $entityManager->persist($resource);
            $entityManager->flush();
            $this->addFlash('success', 'La ressource a bien été vendue');
            return $this->redirectToRoute('app_distributeur_vendu');
        }

        $resources = $resourceRepo->findBy(['currentOwner' => $this->getUser(), 'IsLifeCycleOver' => true], ['date' => 'DESC'], limit: 30);

        return $this->render('pro/distributeur/vente.html.twig', [
            'form' => $form->createView(),
            'resources' => $resources
        ]);
    }
}
