<?php

namespace App\Controller;

use App\Form\ResourceOwnerChangerType;
use App\Handlers\ResourcesListHandler;
use App\Handlers\TransactionHandler;
use App\Handlers\UsineHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\ProductionSiteRepository;
use App\Repository\RecipeRepository;
use App\Repository\ResourceFamilyRepository;
use App\Repository\ResourceNameRepository;
use App\Repository\ResourceRepository;
use App\Service\BlockChainService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/usine')]
class UsineController extends AbstractController
{
    private TransactionHandler $transactionHandler;
    private UsineHandler $usineHandler;
    private BlockChainService $blockChainService;
    private ProductionSiteRepository $productionSiteRepository;

    public function __construct(
        TransactionHandler $transactionHandler,
        UsineHandler $usineHandler,
        BlockChainService $blockChainService,
        ProductionSiteRepository $productionSiteRepository) {
        $this->transactionHandler = $transactionHandler;
        $this->usineHandler = $usineHandler;
        $this->blockChainService = $blockChainService;
        $this->productionSiteRepository = $productionSiteRepository;
    }

    #[Route('/', name: 'app_usine_index')]
    public function index(): Response
    {
        return $this->render('pro/usine/index.html.twig');
    }

    #[Route('/arrivage', name: 'app_usine_acquire')]
    public function acquire(Request $request,
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
                return $this->redirectToRoute('app_usine_acquire');
            }
        }
        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/usine/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests,
        ]);
    }

    #[Route('/list/{category}', name: 'app_usine_list')]
    public function list(ResourcesListHandler $listHandler,
        Request $request,
        $category): Response {
        switch ($category) {
            case 'Demi%20Carcass':
            case 'Demi Carcass':
                $category = 'Demi Carcass';
                break;
            case 'morceau':
                $category = "Meat";
                break;
            default:
                $category = "Product";
                break;
        }
        if ($request->isMethod('POST')) {
            $resources = $this->blockChainService->getRessourceFromTokenId($request->request->get('NFC'));
            $category = $resources["resourceType"];
            if ($resources == []) {
                $this->addFlash('error', 'Aucune ressource trouvée');
                return $this->redirectToRoute('app_usine_list', ['category' => $category]);
            }
            if ($resources['current_owner'] != $this->getUser()->getWalletAddress()) {
                $this->addFlash('error', 'Vous n\'êtes pas le propriétaire de cette ressource');
                return $this->redirectToRoute('app_usine_list', ['category' => $category]);
            }
            return $this->redirectToRoute('app_usine_specific', ['id' => $resources["tokenID"], 'category' => $category]);
        } else {
            $resources = $this->blockChainService->getAllRessourceFromWalletAddress($this->getUser()->getWalletAddress(), $category);
        }
        return $this->render('pro/usine/list.html.twig',
            ['resources' => $resources,
                'category' => $category]
        );

    }

    #[Route('/specific/{id}/{category}', name: 'app_usine_specific')]
    public function specific(ResourceRepository $resourceRepo,
        $id, $category): Response {
        switch ($category) {
            case 'Demi%20Carcass':
            case 'Demi Carcass':
                $resource = $this->blockChainService->getResourceFromTokenIDDemiCarcass($id);
                break;
            case 'Meat':
                $resource = $this->blockChainService->getResourceFromTokenIDMeat($id);
                break;
            default:
                $resource = $this->blockChainService->getResourceFromTokenIDRecipe($id);
                break;
        }

        // dd($resource);

        return $this->render('pro/usine/specific.html.twig', [
            'resource' => $resource,
            'category' => $category,
            'productionSiteName' => $this->getUser()->getProductionSite()->getProductionSiteName(),
        ]);
    }

    #[Route('/decoupe/{id}', name: 'app_usine_decoupe')]
    public function decoupe(Request $request,
        ResourceRepository $resourceRepository,
        ResourceNameRepository $nameRepository,
        $id): Response {
        $demiCarcasse = $this->blockChainService->getRessourceFromTokenId($id);
        $walletAddress = $this->getUser()->getWalletAddress();
        $tokenId = $demiCarcasse["tokenID"];
        $metaData = $this->blockChainService->getStringDataFromTokenID($tokenId);
        $productionSite = $this->productionSiteRepository->findOneby(["id" => $this->getUser()->getProductionSite()->getId()]);
        $this->blockChainService->replaceMetaData($this->getUser()->getWalletAddress(), $tokenId, [
            "meatDate" => new \DateTime('now', new \DateTimeZone('Europe/Paris')),
            "manufacturingPlace" => $productionSite->getAddress(),
            "manufactureingCountry" => $productionSite->getCountry(),
            "approvalNumberManufacturer" => $productionSite->getApprovalNumber(),
        ]);
        sleep(5);
        $morceaux = $this->blockChainService->mintToMany($walletAddress, $tokenId,
            $this->blockChainService->metadataTemplateMeat([
                "meatDate" => new \DateTime('now', new \DateTimeZone('Europe/Paris')),
                "manufacturingPlace" => $productionSite->getAddress(),
                "manufactureingCountry" => $productionSite->getCountry(),
                "approvalNumberManufacturer" => $productionSite->getApprovalNumber(),
            ]));
        $arrayMorceauID = [];
        $arrayMorceauName = [];
        foreach ($morceaux as $key => $morceau) {
            $this->addFlash('success', 'Le morceau ' . $morceau["ressourceName"] . ' a bien été créé avec le tokenID ' . $morceau["tokenId"] . ' et a été ajouté à votre wallet');
            array_push($arrayMorceauID, $morceau["tokenId"]);
            array_push($arrayMorceauName, $morceau["ressourceName"]);
            $newTokenID = $morceau["tokenId"];
        }
        return $this->render('user/WriteOnNFC.html.twig', [
            'id' => $arrayMorceauID,
            'name' => $arrayMorceauName,
            'resourceType' => "Meat",
        ]);
    }

    #[Route('/creationRecette/name', name: 'app_usine_creationRecetteName')]
    public function creationRecetteName(ResourceFamilyRepository $repoFamily): Response
    {
        $families = $repoFamily->findAll();
        return $this->render('pro/usine/creationRecetteName.html.twig',
            [
                'families' => $families,
            ]);
    }

    #[Route('/creationRecette/ingredients', name: 'app_usine_creationRecetteIngredients')]
    public function creationRecetteIngredients(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            if ($this->usineHandler->nameAlreadyExists($name, $this->getUser()->getProductionSite())) {
                $this->addFlash('error', 'Ce nom de recette est déjà utilisé');
                return $this->redirectToRoute('app_usine_creationRecetteName');
            }
            $families = $request->request->all()['families'];
            $ingredients = $this->usineHandler->getAllPossibleIngredients($families);
            return $this->render('pro/usine/creationRecetteIngredients.html.twig', [
                'name' => $name,
                'ingredients' => $ingredients,
            ]);
        }
        return $this->redirectToRoute('app_usine_creationRecetteName');
    }

    #[Route('/creationRecette/process', name: 'app_usine_creationRecetteProcess')]
    public function creationRecetteProcess(Request $request): RedirectResponse
    {
        if ($request->isMethod('POST')) {
            $list = $request->request->all()['list']; //an Array like [['ingredient' => 'name', 'quantity' => 'quantity'], ...]
            $name = $request->request->get('name');
            try {
                $this->usineHandler->recipeCreatingProcess($list, $name, $this->getUser());
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_usine_creationRecetteName');
            }
            $this->addFlash('success', 'La recette a bien été enregistrée');
            return $this->redirectToRoute('app_usine_index');
        }
        return $this->redirectToRoute('app_usine_creationRecetteName');
    }

    #[Route('/choixRecette', name: 'app_usine_choixRecette')]
    public function choixRecette(ResourceNameRepository $nameRepo): Response
    {
        $recettes = $this->blockChainService->getAllRecipe("MANUFACTURER");
        return $this->render('pro/usine/choixRecette.html.twig', [
            'titles' => $recettes,
        ]);
    }

    #[Route('/recette/{id}', name: 'app_usine_recette')]
    public function appliRecette($id,
        Request $request,
        RecipeRepository $recipeRepo,
        ResourceNameRepository $nameRepo): Response {
        $recipe = $this->blockChainService->getRecipe($id);
        $ingredients = $this->blockChainService->getResourceListInformation($recipe["needed_resources"]);
        // dd($ingredients);
        $recipeTitle = $recipe["resource_name"];
        if ($request->isMethod('POST')) {
            $morceaux = $request->request->all()['morceaux'];
            $template = [
                "manufacturingPlace" => $this->getUser()->getProductionSite()->getAddress(),
                "recipeDate" => new \DateTime('now', new \DateTimeZone('Europe/Paris')),
                "manufactureingCountry" => $this->getUser()->getProductionSite()->getCountry(),
                "approvalNumberManufacturer" => $this->getUser()->getProductionSite()->getApprovalNumber(),
            ];

            try {
                

                $mintResource = $this->blockChainService->mintResource($this->getUser()->getWalletAddress(),
                                                                        $id,
                                                                        1, 
                                                                        $this->blockChainService->metadataTemplateRecipe($template),
                                                                        $morceaux);
                $mintResource = json_decode($mintResource, true);
                // dd($mintResource);

            } catch (\Exception $e) {
                $this->addFlash('error', "Vérifiez votre stock et le NFT" );
                return $this->redirectToRoute('app_usine_recette', ['id' => $id]);
            }
            $returnID = [$mintResource["tokenId"]];
            $returnName = [$mintResource["ressourceName"]];
            $this->addFlash('success', 'Recette bien appliquée, vous avez crée : ' . $mintResource["ressourceName"] . ' ! NFT : ' . $mintResource["tokenId"]);

            return $this->render('user/WriteOnNFC.html.twig', [
                'id' => $returnID,
                'name' => $returnName,
                'resourceType' => "Product",
            ]);
        }
        return $this->render('pro/usine/appliRecette.html.twig',
            ['ingredients' => $ingredients, 'product' => $recipeTitle]);
    }

    #[Route('/transaction', name: 'app_usine_transferList')]
    public function transferList(OwnershipAcquisitionRequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser(), 'state' => 'En attente']);
        $pastTransactions = $requestRepository->findPastRequests($this->getUser());
        return $this->render('pro/usine/transferList.html.twig',
            ['requests' => $requests, 'pastTransactions' => $pastTransactions]
        );
    }

    #[Route('/transaction/{id}', name: 'app_usine_transfer', requirements: ['id' => '\d+'])]
    public function transfer($id): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptTransaction($id, $this->getUser());
            $this->addFlash('success', 'Transaction effectuée');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_usine_transferList');
        }
    }

    #[Route('/transactionRefused/{id}', name: 'app_usine_transferRefused', requirements: ['id' => '\d+'])]
    public function transferRefused($id): RedirectResponse
    {
        try {
            $this->transactionHandler->refuseTransaction($id, $this->getUser());
            $this->addFlash('success', 'Transaction refusée avec succès');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_usine_transferList');
        }
    }

    #[Route('/transaction/all', name: 'app_usine_transferAll')]
    public function transferAll(): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptAllTransactions($this->getUser());
            $this->addFlash('success', 'Toutes les transactions ont été effectuées');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_usine_transferList');
        }
    }

}
