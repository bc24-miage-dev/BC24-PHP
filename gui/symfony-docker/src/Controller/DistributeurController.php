<?php

namespace App\Controller;

use App\Handlers\proAcquireHandler;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                                ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $proAcquireHandler = new proAcquireHandler();

            if($proAcquireHandler->acquireStrict($form, $doctrine, $this->getUser(), 'PRODUIT')){
                $this->addFlash('success', 'Le produit a bien été enregistré');
            }
            else{
                $this->addFlash('error', 'Ce tag NFC ne correspond pas à un produit');
            }
            return $this->redirectToRoute('app_distributeur_acquire');
        }
        return $this->render('pro/distributeur/acquire.html.twig', [
            'form' => $form->createView()
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

        return $this->render('pro/distributeur/vente.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
