<?php

namespace App\Controller;

use App\Form\EquarrisseurAnimalAbattageFormType;
use App\Form\ResourceOwnerChangerType;
use App\Handlers\EquarrisseurHandler;
use App\Handlers\ResourceHandler;
use App\Handlers\ResourcesListHandler;
use App\Handlers\TransactionHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ResourceNameRepository;
use App\Repository\ResourceRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\BlockChainService;
use App\Repository\ProductionSiteRepository;

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
                                ProductionSiteRepository $productionSiteRepository)
    {
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
                                OwnershipAcquisitionRequestRepository $ownershipRepo): Response
    {
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
            'requests' => $requests
        ]);
    }


    #[Route('/list/{category}', name: 'app_equarrisseur_list')]
    public function list(ResourcesListHandler $listHandler,
                         String $category,
                         Request $request) : Response
    {
        if ($category === "Demi%20Carcass") {
            $category = 'Demi Carcass';
        }
        $resources =$this->blockChainService->getAllRessourceFromWalletAddress($this->getUser()->getWalletAddress(),$category);

        return $this->render('pro/equarrisseur/list.html.twig',
            ['resources' => $resources,
                'category' => $category]
        );
    }

    #[Route('/specific/{id}/{category}', name: 'app_equarrisseur_job')]
    public function job($id,$category): Response
    {
        switch (strtolower($category)) {
            case 'animal':
                $nextCategory = "Carcass";
                break;
            case 'carcass':
                $nextCategory = "Demi Carcass";
                break;
            default:
                $nextCategory = "Demi Carcass";
                break;
        }
        $resource =$this->blockChainService->getRessourceFromTokenId($id);
        $possibleResource = $this->blockChainService->getPossibleResourceFromResourceID($resource["resourceID"], "SLAUGHTERER", $nextCategory);

            return $this->render('pro/equarrisseur/job.html.twig', [
                'resource' => $resource,
                'newResourceID' => $possibleResource[0]["resource_id"],
                'category' => $category,
                'country' => $this->getUser()->getProductionSite()->getCountry()
            ]);
    }

    #[Route('/equarrir/{newResourceID}/{tokenIDAnimal}', name: 'app_equarrisseur_equarrir')]
    public function equarrir(ResourceHandler $handler,
                             Request $request,
                             $newResourceID, $tokenIDAnimal) : Response
    {

        $form = $this->createForm(EquarrisseurAnimalAbattageFormType::class);
        $form->handleRequest($request);
        $currentResource = $this->blockChainService->getRessourceFromTokenId($tokenIDAnimal);
        $type = $currentResource["resourceType"];
        if ($form->isSubmitted()) {
            $walletAddress = $this->getUser()->getWalletAddress();
            $carcass = $this->blockChainService->getResourceTemplate($newResourceID, "SLAUGHTERER");
            $carcassID = $carcass[0]["resource_id"];
            $ingredient = $tokenIDAnimal;
            $getMetaData = $this->blockChainService->getStringDataFromTokenID($tokenIDAnimal);
            // $currentResource = $this->blockChainService->getRessourceFromTokenId($tokenIDAnimal);
            $resourceType = $carcass[0]["resource_type"];
            
            if($newResourceID == $currentResource["resourceID"])// quand il ne trouve pas de resource à fabriquer alors on doit utiliser mintToMany
            {                                                   // on le sait quand la nouvelle resource théorique est la même que la resource actuelle
                $newResourceType = "Demi Carcass";
                //dd($walletAddress, $tokenIDAnimal , $getMetaData);
                $mintResource = $this->blockChainService->mintToMany($walletAddress, $tokenIDAnimal , $getMetaData);
                sleep(7);
                // $mintResource = json_decode($mintResource, true);


                $returnID = [];
                $returnName = [];
                foreach ($mintResource as $key => $mintedResource) {
                    $newTokenID = $mintedResource["tokenId"];
                    array_push($returnID, $mintedResource["tokenId"]);
                    array_push($returnName, $mintedResource["ressourceName"]);

                    $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(),$newTokenID,[
                        "demiCarcassDate" => new \DateTime('now', new \DateTimeZone('Europe/Paris'))
                    
                    ]);
                    sleep(7);

                    $this->addFlash('success', 'Votre carcass à bien été découpée en : '.$newResourceType.' ! NFT : ' . $mintedResource["tokenId"]);
                }

                return $this->render('user/WriteOnNFC.html.twig', [
                    'id' => $returnID,
                    'name' => $returnName,
                    'resourceType' => $newResourceType,
                ]);
                
            }
            else
            {
                
                $newResourceType = "Carcass";
                // dd($walletAddress, $carcassID, $getMetaData, [$ingredient]);
                $mintResource = $this->blockChainService->mintResource($walletAddress,$carcassID,1, $getMetaData, [$ingredient]);
            }
            sleep(7);
            $responseArray = json_decode($mintResource, true);
            $newTokenID = $responseArray["tokenId"];
            $productionSite = $this->productionSiteRepository->findOneby(["id" => $this->getUser()->getProductionSite()->getId()]);
            $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(),$newTokenID,[
                "slaughteringPlace" => $this->getUser()->getProductionSite()->getAddress(),
                "carcassDate" => new \DateTime('now', new \DateTimeZone('Europe/Paris')),
                "slaughtererCountry" => $this->getUser()->getProductionSite()->getCountry(),
                "approvalNumberSlaughterer" => $productionSite->getApprovalNumber(),

            
            ]);
            $this->addFlash('success', 'Votre animal à bien été transformé en : carcass ! NFT : ' . $responseArray["tokenId"]);
            return $this->render('user/WriteOnNFC.html.twig', [
                'id' => [$responseArray['tokenId']],
                'name' =>  [$responseArray['ressourceName']],
                'resourceType' => $newResourceType,
            ]);
        }
        return $this->render('pro/equarrisseur/equarrir.html.twig', [
            "id" => $newResourceID,
            "tokenID" => $tokenIDAnimal,
            'form' => $form->createView(),
            'resourceType' => $type
        ]);
    }

    #[Route('/decoupe/{id}', name: 'app_equarrisseur_decoupe')]
    public function decoupe(Request $request,
                            $id) : Response
    {
        $resource = $this->resourceRepository->findOneBy(['id'=> $id]);
        if (!$this->equarrisseurHandler->canSlice($resource, $this->getUser()))
        {
            $this->addFlash('error', 'Il y a eu un problème, veuillez contacter un administrateur');
            return $this->redirectToRoute('app_equarrisseur_list', ['category' => 'CARCASSE']);
        }

        //Classic form because two different entities must be processed at once
        if ($request->isMethod('POST')) {
            try {
            $this->equarrisseurHandler->slicingProcess($resource, $this->getUser(), $request);
            } catch(UniqueConstraintViolationException){
                $this->addFlash('error', 'Au moins un des tags NFC existe déjà');
                return $this->redirectToRoute('app_equarrisseur_decoupe', ['id' => $id]);
            }
            catch(Exception $e) {
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