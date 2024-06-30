<?php

namespace App\Controller;

use App\Form\ResourceOwnerChangerType;
use App\Handlers\ProHandler;
use App\Handlers\ResourcesListHandler;
use App\Handlers\TransactionHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ResourceRepository;
use App\Service\BlockChainService;
use App\Service\HardwareService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/transporteur')]
class TransporteurController extends AbstractController
{

    private TransactionHandler $transactionHandler;
    private ProHandler $proHandler;
    private BlockChainService $blockChainService;
    private HardwareService $hardwareService;

    public function __construct(TransactionHandler $transactionHandler,
        ProHandler $proHandler,
        BlockChainService $blockChainService,
        HardwareService $hardwareService) {
        $this->transactionHandler = $transactionHandler;
        $this->proHandler = $proHandler;
        $this->blockChainService = $blockChainService;
        $this->hardwareService = $hardwareService;
    }

    #[Route('/', name: 'app_transporteur_index')]
    public function index(): Response
    {
        return $this->render('pro/transporteur/index.html.twig');
    }

    #[Route('/acquisition', name: 'app_transporteur_acquire')]
    public function acquisition(Request $request,
        OwnershipAcquisitionRequestRepository $ownershipRepo): Response {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->transactionHandler->askOwnership($this->getUser(), $form->getData()["id"]);
                $this->addFlash('success', 'La demande de propriété a bien été envoyée');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            } finally {
                return $this->redirectToRoute('app_transporteur_acquire');
            }
        }
        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/transporteur/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests,
        ]);
    }

    #[Route('/list', name: 'app_transporteur_list')]
    public function list(ResourcesListHandler $listHandler,
        Request $request): Response {
        if ($request->isMethod('POST')) {
            $resources = $this->blockChainService->getRessourceFromTokenId($request->request->get('NFC'));
            $category = $resources["resourceType"];
            if ($resources == []) {
                $this->addFlash('error', 'Aucune ressource trouvée');
                return $this->redirectToRoute('app_transporteur_list');
            }
            if ($resources['current_owner'] != $this->getUser()->getWalletAddress()) {
                $this->addFlash('error', 'Vous n\'êtes pas le propriétaire de cette ressource');
                return $this->redirectToRoute('app_transporteur_list');
            }
            return $this->redirectToRoute('app_transporteur_specific', ['id' => $resources["tokenID"]]);
        } else {
            $resources = $this->blockChainService->getAllRessourceFromWalletAddress($this->getUser()->getWalletAddress());
        }
        return $this->render('pro/transporteur/list.html.twig',
            ['resources' => $resources]
        );
    }

    #[Route('/specific/{id}', name: 'app_transporteur_specific')]
    public function specific(ResourceRepository $resourceRepository,
        $id): Response {
        $resource = $this->blockChainService->getRessourceFromTokenId($id);

        switch ($resource["resourceType"]) {
            case 'Animal':
                $resources = $this->blockChainService->getResourceFromTokenIDAnimal($id);
                break;
            case "Carcass":
                $resources = $this->blockChainService->getResourceFromTokenIDCarcass($id);
                break;
            case "Demi Carcass":
                $resources = $this->blockChainService->getResourceFromTokenIDDemiCarcass($id);
                break;
            case "Meat":
                $resources = $this->blockChainService->getResourceFromTokenIDMeat($id);
                break;
            case "Product":
                $resources = $this->blockChainService->getResourceFromTokenIDProduct($id);
                break;
            default:
                $this->addFlash('error', 'Ressource non reconnue');
                return $this->redirectToRoute('app_transporteur_list');
                break;
        }

        return $this->render('pro/transporteur/specific.html.twig', [
            'resource' => $resources,
        ]);
    }

    #[Route('/transaction', name: 'app_transporteur_transferList')]
    public function transferList(OwnershipAcquisitionRequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser(), 'state' => 'En attente']);
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
        } catch (\Exception $e) {
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

    #[Route('/transaction/all', name: 'app_transporteur_transferAll')]
    public function transferAll(): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptAllTransactions($this->getUser());
            $this->addFlash('success', 'Toutes les transactions ont été effectuées');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_transporteur_transferList');
        }
    }

    #[Route('/start', name: 'app_transporteur_start')]
    public function start(): Response
    {
        try {
            $readerData = $this->hardwareService->startReader();
            $readerData = json_decode($readerData->getContent(), true);
            //dd($readerData);
            $resourcesTransported = $this->blockChainService->getAllRessourceFromWalletAddress($this->getUser()->getWalletAddress());
            //dd($resourcesTransported);
            $arrayAddMetadata = [
                "gpsStart" => $readerData["data"]["gps"],
                "temperatureStart" => $readerData["data"]["temperature"],
            ];
            foreach ($resourcesTransported as $resource) {
                $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(), $resource["tokenId"], $arrayAddMetadata);
                sleep(5);
            }
            $this->addFlash('success', 'Le transport à démaré');

        } catch (Exception $th) {
            $this->addFlash('error', 'Erreur lors du départ');
        }
        return $this->redirectToRoute('app_transporteur_list');
    }

    #[Route('/end', name: 'app_transporteur_end')]
    public function end(): Response
    {
        try {
            $readerData = $this->hardwareService->startReader();
            $readerData = json_decode($readerData->getContent(), true);

            $resourcesTransported = $this->blockChainService->getAllRessourceFromWalletAddress($this->getUser()->getWalletAddress());
            //dd($resourcesTransported);
            $arrayAddMetadata = [
                "gpsEnd" => $readerData["data"]["gps"],
                "temperatureEnd" => $readerData["data"]["temperature"],
            ];
            foreach ($resourcesTransported as $resource) {
                $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(), $resource["tokenId"], $arrayAddMetadata);
                sleep(5);
            }
            $this->addFlash('success', 'Le transport s\'est terminé');

        } catch (Exception $th) {
            $this->addFlash('error', 'Erreur lors de l\'arrivée');
        }
        return $this->redirectToRoute('app_transporteur_list');
    }
}
