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

#[Route('/pro/equarrisseur')]
class EquarrisseurController extends AbstractController
{
    private TransactionHandler $transactionHandler;
    private EquarrisseurHandler $equarrisseurHandler;
    private ResourceRepository $resourceRepository;
    private BlockChainService $blockChainService;

    public function __construct(TransactionHandler $transactionHandler,
                                EquarrisseurHandler $equarrisseurHandler,
                                ResourceRepository $resourceRepository,
                                BlockChainService $blockChainService)
    {
        $this->transactionHandler = $transactionHandler;
        $this->equarrisseurHandler = $equarrisseurHandler;
        $this->resourceRepository = $resourceRepository;
        $this->blockChainService = $blockChainService;
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
                $this->transactionHandler->askOwnership($form->getData()->getId(), $this->getUser());
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


    #[Route('/list/{category}', name: 'app_equarrisseur_list')] // An 'Equarrisseur' have access to the list of his animals and carcasses
    public function list(ResourcesListHandler $listHandler,
                         String $category,
                         Request $request) : Response
    {
        $resources =$this->blockChainService->getAllRessourceFromWalletAddress($this->getUser()->getWalletAddress(),"ANIMAL");
        // dd($resources);

        // if ($request->isMethod('POST')) {
        //     try {
        //         $resources = $listHandler->getSpecificResource($request->request->get('NFC'), $this->getUser());
        //     }
        //     catch (\Exception $e) {
        //         $this->addFlash('error', $e->getMessage());
        //         return $this->redirectToRoute('app_equarrisseur_list', ['category' => $category] );
        //     }
        // }
        // else{
        //     $resources = $listHandler->getResources($this->getUser(), $category);
        // }

        return $this->render('pro/equarrisseur/list.html.twig',
            ['resources' => $resources]
        );
    }

    #[Route('/specific/{id}', name: 'app_equarrisseur_job')]
    public function job($id): Response
    {
        $resource =$this->blockChainService->getRessourceFromTokenId($id);
        // dd($resource);
        $possibleResource = $this->blockChainService->getPossibleResourceFromResourceID($resource["resourceID"], "SLAUGHTERER", "Carcass");
        // dd($test);
        // $resource = $this->resourceRepository->findOneBy(['id' => $id]);

        // if (!$this->equarrisseurHandler->canHaveAccess($resource, $this->getUser())){
        //     $this->addFlash('error', 'Ressource introuvable');
        //     return $this->redirectToRoute('app_equarrisseur_list', ['category' => 'ANIMAL']);
        // }

        // $category = $resource->getResourceName()->getResourceCategory()->getCategory();
            return $this->render('pro/equarrisseur/job.html.twig', [
                'resource' => $resource,
                'newResourceID' => $possibleResource[0]["resource_id"],
            ]);
    }

    #[Route('/equarrir/{newResourceID}/{tokenIDAnimal}', name: 'app_equarrisseur_equarrir')]
    public function equarrir(ResourceHandler $handler,
                             Request $request,
                             $newResourceID, $tokenIDAnimal) : Response
    {

        $form = $this->createForm(EquarrisseurAnimalAbattageFormType::class);
        $form->handleRequest($request);
        // dd($form);
        if ($form->isSubmitted()) {
            $walletAddress = $this->getUser()->getWalletAddress();
            $carcass = $this->blockChainService->getResourceTemplate($newResourceID, "SLAUGHTERER");
            $carcassID = $carcass[0]["resource_id"];
            // dd($carcass);
            $ingredient = $tokenIDAnimal;
            // dd($carcass);
            $getMetaData = $this->blockChainService->getStringDataFromTokenID($tokenIDAnimal);
            // dd($getMetaData);
            $mintResource = $this->blockChainService->mintResource($walletAddress,$carcassID,1, $getMetaData, [$ingredient]);
            $responseArray = json_decode($mintResource, true);
            $this->addFlash('success', 'Votre animal à bien été transformé en : '.$responseArray["ressourceName"].' ! NFT : ' . $responseArray["tokenId"]);
            return $this->redirectToRoute('app_nfc_write', ['id' => $responseArray['tokenId']]);
            // $test2 = $this->blockChainService->mintResource();
            // dd($mintResource);

        }
        return $this->render('pro/equarrisseur/equarrir.html.twig', [
            "id" => $newResourceID,
            "tokenID" => $tokenIDAnimal,
            'form' => $form->createView()
        ]);


        // $resource = $this->resourceRepository->findOneBy(['id' => $id]);
        // if (!$this->equarrisseurHandler->canSlaughter($resource, $this->getUser())) {
        //     $this->addFlash('error', 'Une erreur est survenue, veuillez contacter un administrateur');
        //     return $this->redirectToRoute('app_equarrisseur_list', ['category' => 'ANIMAL']);
        // }

        // $form = $this->createForm(EquarrisseurAnimalAbattageFormType::class);
        //     $newCarcasse = $handler->createChildResource($resource, $this->getUser()));
        // $form->handleRequest($request);
        // if ($form->isSubmitted() && $form->isValid()) {
        //     try {
        //         $this->equarrisseurHandler->slaughteringProcess($resource, $newCarcasse);
        //     } catch (UniqueConstraintViolationException) {
        //         $this->addFlash('error', 'Le tag NFC existe déjà');
        //         return $this->redirectToRoute('app_equarrisseur_equarrir', ['id' => $id]);
        //     }
        //     $this->addFlash('success', 'L\'animal a bien été abattu, une carcasse a été créée');
        //     return $this->redirectToRoute('app_equarrisseur_list', ['category' => 'CARCASSE']);
        // }
        return $this->render('pro/equarrisseur/equarrir.html.twig', [
            'form' => $form->createView()
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
