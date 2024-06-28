<?php

namespace App\Controller;

use App\Form\EquarrisseurAnimalAbattageFormType;
use App\Form\ResourceOwnerChangerType;
use App\Handlers\EquarrisseurHandler;
use App\Handlers\ResourceHandler;
use App\Handlers\ResourcesListHandler;
use App\Handlers\TransactionHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ProductionSiteRepository;
use App\Repository\ResourceRepository;
use App\Service\BlockChainService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/equarrisseur')]
class EquarrisseurController extends AbstractController
{
    private TransactionHandler $transactionHandler;
    private EquarrisseurHandler $equarrisseurHandler;
    private ResourceRepository $resourceRepository;
    private BlockChainService $blockChainService;
    private ProductionSiteRepository $productionSiteRepository;

    public function __construct(TransactionHandler $transactionHandler,
        EquarrisseurHandler $equarrisseurHandler,
        ResourceRepository $resourceRepository,
        BlockChainService $blockChainService,
        ProductionSiteRepository $productionSiteRepository) {
        $this->transactionHandler = $transactionHandler;
        $this->equarrisseurHandler = $equarrisseurHandler;
        $this->resourceRepository = $resourceRepository;
        $this->blockChainService = $blockChainService;
        $this->productionSiteRepository = $productionSiteRepository;
    }

    #[Route('/', name: 'app_equarrisseur_index')]
    public function index(): Response
    {
        return $this->render('pro/equarrisseur/index.html.twig');
    }

    #[Route('/acquisition', name: 'app_equarrisseur_acquire')]
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
                return $this->redirectToRoute('app_equarrisseur_acquire');
            }
        }
        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/equarrisseur/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests,
        ]);
    }

    #[Route('/list/{category}', name: 'app_equarrisseur_list')]
    public function list(ResourcesListHandler $listHandler,
        String $category,
        Request $request): Response {
        if ($category === "Demi%20Carcass") {
            $category = 'Demi Carcass';
        }
        if ($request->isMethod('POST')) {
            $resources = $this->blockChainService->getRessourceFromTokenId($request->request->get('NFC'));
            $category = $resources["resourceType"];
            if ($resources == []) {
                $this->addFlash('error', 'Aucune ressource trouvée');
                return $this->redirectToRoute('app_equarrisseur_list', ['category' => $category]);
            }
            if($resources['current_owner'] != $this->getUser()->getWalletAddress()){
                $this->addFlash('error', 'Vous n\'êtes pas le propriétaire de cette ressource');
                return $this->redirectToRoute('app_equarrisseur_list', ['category' => $category]);
            }
            return $this->redirectToRoute('app_equarrisseur_job', ['id' => $resources["tokenID"], 'category' => $category]);
        } else {
            $resources = $this->blockChainService->getAllRessourceFromWalletAddress($this->getUser()->getWalletAddress(), $category);
        }

        return $this->render('pro/equarrisseur/list.html.twig',
            ['resources' => $resources,
                'category' => $category]
        );
    }

    #[Route('/specific/{id}/{category}', name: 'app_equarrisseur_job')]
    public function job($id, $category): Response
    {
        switch (strtolower($category)) {
            case 'animal':
                $nextCategory = "Carcass";
                $resource = $this->blockChainService->getResourceFromTokenIDAnimal($id);
                break;
            case 'carcass':
                $nextCategory = "Demi Carcass";
                $resource = $this->blockChainService->getResourceFromTokenIDCarcass($id);
                break;
            default:
                $nextCategory = "Demi Carcass";
                $resource = $this->blockChainService->getResourceFromTokenIDDemiCarcass($id);
                break;
        }
        $possibleResource = $this->blockChainService->getPossibleResourceFromResourceID($resource["resourceID"], "SLAUGHTERER", $nextCategory);

        return $this->render('pro/equarrisseur/job.html.twig', [
            'resource' => $resource,
            'newResourceID' => $possibleResource[0]["resource_id"],
            'category' => $category,
            'country' => $this->getUser()->getProductionSite()->getCountry(),
        ]);
    }

    #[Route('/equarrir/{newResourceID}/{tokenIDAnimal}', name: 'app_equarrisseur_equarrir')]
    public function equarrir(ResourceHandler $handler,
        Request $request,
        $newResourceID, $tokenIDAnimal): Response {

        $form = $this->createForm(EquarrisseurAnimalAbattageFormType::class);
        $form->handleRequest($request);
        $currentResource = $this->blockChainService->getRessourceFromTokenId($tokenIDAnimal);
        $type = $currentResource["resourceType"];
        if ($form->isSubmitted()) {
            $walletAddress = $this->getUser()->getWalletAddress();
            $carcass = $this->blockChainService->getResourceTemplate($newResourceID, "SLAUGHTERER");
            $carcassID = $carcass[0]["resource_id"];
            $ingredient = $tokenIDAnimal;
            $resourceType = $carcass[0]["resource_type"];
            $productionSite = $this->productionSiteRepository->findOneby(["id" => $this->getUser()->getProductionSite()->getId()]);
            if ($newResourceID == $currentResource["resourceID"]) // quand il ne trouve pas de resource à fabriquer alors on doit utiliser mintToMany
            { // on le sait quand la nouvelle resource théorique est la même que la resource actuelle
                $newResourceType = "Demi Carcass";
                $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(), $tokenIDAnimal, [
                    "demiCarcassDate" => new \DateTime('now', new \DateTimeZone('Europe/Paris')),
                    "slaughteringPlace" => $productionSite->getAddress(),
                    "approvalNumberSlaughterer" => $productionSite->getApprovalNumber(),
                    'slaughtererCountry' => $productionSite->getCountry(),
                ]);
                sleep(5);
                $mintResource = $this->blockChainService->mintToMany($walletAddress, $tokenIDAnimal,
                    $this->blockChainService->metadataTemplateDemiCarcass([
                        "demiCarcassDate" => new \DateTime('now', new \DateTimeZone('Europe/Paris')),
                        "slaughteringPlace" => $productionSite->getAddress(),
                        "approvalNumberSlaughterer" => $productionSite->getApprovalNumber(),
                        'slaughtererCountry' => $productionSite->getCountry(),
                    ]
                    )
                );
                sleep(5);
                $returnID = [];
                $returnName = [];
                foreach ($mintResource as $key => $mintedResource) {
                    $newTokenID = $mintedResource["tokenId"];
                    array_push($returnID, $mintedResource["tokenId"]);
                    array_push($returnName, $mintedResource["ressourceName"]);
                    $this->addFlash('success', 'Votre carcass à bien été découpée en : ' . $newResourceType . ' ! NFT : ' . $mintedResource["tokenId"]);
                }

                return $this->render('user/WriteOnNFC.html.twig', [
                    'id' => $returnID,
                    'name' => $returnName,
                    'resourceType' => $newResourceType,
                ]);

            } else {
                //case this is an animal to carcass
                $newResourceType = "Carcass";
                $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(), $tokenIDAnimal, [
                    "carcassDate" => new \DateTime('now', new \DateTimeZone('Europe/Paris')),
                    "slaughteringPlace" => $productionSite->getAddress(),
                    "approvalNumberSlaughterer" => $productionSite->getApprovalNumber(),
                    'slaughtererCountry' => $productionSite->getCountry(),
                ]);
                sleep(5);
                $mintResource = $this->blockChainService->mintResource($walletAddress, $carcassID, 1,
                    $this->blockChainService->metadataTemplateCarcass([
                        "carcassDate" => new \DateTime('now', new \DateTimeZone('Europe/Paris')),
                        "slaughteringPlace" => $productionSite->getAddress(),
                        "approvalNumberSlaughterer" => $productionSite->getApprovalNumber(),
                        'slaughtererCountry' => $productionSite->getCountry(),
                    ]), [$ingredient]);
            }
            sleep(5);
            $responseArray = json_decode($mintResource, true);
            $newTokenID = $responseArray["tokenId"];
            $this->addFlash('success', 'Votre animal à bien été transformé en : carcass ! NFT : ' . $responseArray["tokenId"]);
            return $this->render('user/WriteOnNFC.html.twig', [
                'id' => [$responseArray['tokenId']],
                'name' => [$responseArray['ressourceName']],
                'resourceType' => $newResourceType,
            ]);
        }
        return $this->render('pro/equarrisseur/equarrir.html.twig', [
            "id" => $newResourceID,
            "tokenID" => $tokenIDAnimal,
            'form' => $form->createView(),
            'resourceType' => $type,
        ]);
    }

    #[Route('/decoupe/{id}', name: 'app_equarrisseur_decoupe')]
    public function decoupe(Request $request,
        $id): Response {
        $resource = $this->resourceRepository->findOneBy(['id' => $id]);
        if (!$this->equarrisseurHandler->canSlice($resource, $this->getUser())) {
            $this->addFlash('error', 'Il y a eu un problème, veuillez contacter un administrateur');
            return $this->redirectToRoute('app_equarrisseur_list', ['category' => 'CARCASSE']);
        }

        //Classic form because two different entities must be processed at once
        if ($request->isMethod('POST')) {
            try {
                $this->equarrisseurHandler->slicingProcess($resource, $this->getUser(), $request);
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'Au moins un des tags NFC existe déjà');
                return $this->redirectToRoute('app_equarrisseur_decoupe', ['id' => $id]);
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_equarrisseur_decoupe', ['id' => $id]);
            }
            $this->addFlash('success', 'Cette carcasse a bien été découpée');
            return $this->redirectToRoute('app_equarrisseur_list', ['category' => 'DEMI-CARCASSE']);
        }
        return $this->render('pro/equarrisseur/slice.html.twig');
    }

    #[Route('/transaction', name: 'app_equarrisseur_transferList')]
    public function transferList(OwnershipAcquisitionRequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser(), 'state' => 'En attente']);
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
        } catch (\Exception $e) {
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

    #[Route('/transaction/all', name: 'app_equarrisseur_transferAll')]
    public function transferAll(): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptAllTransactions($this->getUser());
            $this->addFlash('success', 'Toutes les transactions ont été effectuées');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_equarrisseur_transferList');
        }
    }
}
