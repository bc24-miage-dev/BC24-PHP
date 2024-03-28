<?php

namespace App\Controller;

use App\Handlers\proAcquireHandler;
use App\Repository\ResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ResourceOwnerChangerType;
use App\Entity\Resource;
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
    public function list(ManagerRegistry $doctrine) : Response
    {
        $repository = $doctrine->getRepository(Resource::class);
        $resource = $repository->findBy([
            'currentOwner' => $this->getUser(),
            'IsLifeCycleOver' => false
        ]);
        return $this->render('pro/distributeur/list.html.twig',
            ['resource' => $resource]
        );
    }



    #[Route('/vente', name: 'app_distributeur_vendu')]
    public function vendre(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_DISTRIBUTEUR');

        $form = $this->createForm(ResourceNfcType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nfcTag = $form->get('id')->getData();

            $resource = $doctrine->getRepository(Resource::class)->findOneBy(['id' => $nfcTag]);

            if ($resource && $resource->getCurrentOwner() === $this->getUser()){
                $resource->setIsLifeCycleOver(true);

                $entityManager = $doctrine->getManager();
                $entityManager->flush();

                $this->addFlash('success', 'La ressource a bien été vendue');
                return $this->redirectToRoute('app_distributeur_vendu');
            } else {
                $this->addFlash('danger', 'Resource not found with provided NFC tag.');
            }
        }

        return $this->render('pro/distributeur/vente.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
