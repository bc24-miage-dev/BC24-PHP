<?php

namespace App\Controller;


use App\Handlers\DistributeurHandler;
use App\Handlers\ResourcesListHandler;
use App\Handlers\TransactionHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ResourceOwnerChangerType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ResourceNfcType;

#[Route('/pro/distributeur')]
class DistributeurController extends AbstractController
{

    private TransactionHandler $transactionHandler;

    private DistributeurHandler $distributeurHandler;

    public function __construct(TransactionHandler $transactionHandler, DistributeurHandler $distributeurHandler)
    {
        $this->transactionHandler = $transactionHandler;
        $this->distributeurHandler = $distributeurHandler;
    }

    #[Route('/', name: 'app_distributeur_index')]
    public function index(): Response
    {
        return $this->render('pro/distributeur/index.html.twig');
    }

    #[Route('/acquisition', name: 'app_distributeur_acquire')]
    public function acquisition(Request $request,
                                OwnershipAcquisitionRequestRepository $ownershipRepo): Response
    {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->transactionHandler->askOwnership($form->getData()->getId(), $this->getUser());
                $this->addFlash('success', 'La demande de propriété a bien été envoyée');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            } finally {
                return $this->redirectToRoute('app_distributeur_acquire');
            }
        }
        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/distributeur/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests
        ]);
    }

    #[Route('/list', name: 'app_distributeur_list')]
    public function list(ResourcesListHandler $listHandler,
                         Request $request) : Response
    {
        if ($request->isMethod('POST')) {
            try {
                $resources = $listHandler->getSpecificResource($request->request->get('NFC'), $this->getUser());
            }
            catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_distributeur_list');
            }
        }
        else{
            $resources = $listHandler->getResources($this->getUser());
        }

        return $this->render('pro/distributeur/list.html.twig',
            ['resources' => $resources]
        );
    }

    #[Route('/specific/{id}', name: 'app_distributeur_specific')]
    public function specific(ResourceRepository $resourceRepo, $id) : Response
    {
        $resource = $resourceRepo->findOneBy(['id' => $id]);
        if (!$this->distributeurHandler->canHaveAccess($resource, $this->getUser())) {
            $this->addFlash('error', 'Aucun produit vous appartenant avec cet id n\'a été trouvé');
            return $this->redirectToRoute('app_distributeur_list');
        }
        return $this->render('pro/distributeur/specific.html.twig',
            ['resource' => $resource]
        );
    }



    #[Route('/vente', name: 'app_distributeur_vendu')]
    public function vendre(Request $request): Response
    {
        $form = $this->createForm(ResourceNfcType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try{
                $this->distributeurHandler->saleProcess($form->getData()->getId(), $this->getUser());
                $this->addFlash('success', 'La ressource a bien été vendue');
            }
            catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());

            } finally {
                return $this->redirectToRoute('app_distributeur_vendu');
            }
        }
        return $this->render('pro/distributeur/vente.html.twig', [
            'form' => $form->createView(),
            'resources' => $this->distributeurHandler->getRecentSalesHistory($this->getUser())
        ]);
    }
}
