<?php

namespace App\Controller;

use App\Form\ResourceOwnerChangerType;
use App\Handlers\ResourcesListHandler;
use App\Handlers\TransactionHandler;
use App\Handlers\UsineHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\RecipeRepository;
use App\Repository\ResourceFamilyRepository;
use App\Repository\ResourceNameRepository;
use App\Repository\ResourceRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\BlockChainService;


#[Route('/pro/usine')]
class  UsineController extends AbstractController
{
    private TransactionHandler $transactionHandler;
    private UsineHandler $usineHandler;
    private BlockChainService $blockChainService;

    public function __construct(TransactionHandler $transactionHandler, UsineHandler $usineHandler, BlockChainService $blockChainService)
    {
        $this->transactionHandler = $transactionHandler;
        $this->usineHandler = $usineHandler;
        $this->blockChainService = $blockChainService;
    }

    


    #[Route('/', name: 'app_usine_index')]
    public function index(): Response
    {
        return $this->render('pro/usine/index.html.twig');
    }


    #[Route('/arrivage', name:'app_usine_acquire')]
    public function acquire(Request $request,
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
                return $this->redirectToRoute('app_usine_acquire');
            }
        }
        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/usine/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests
        ]);
    }


    #[Route('/list/{category}', name: 'app_usine_list')]
    public function list(ResourcesListHandler $listHandler,
                         Request $request,
                         $category): Response
    {
        switch ($category) {
            case 'Demi%20Carcass' or 'Demi Carcass':
                $category = 'Demi Carcass';
                break;
            default:
                $category = "Meat";
                break;
        }
        
        if ($category === "Demi%20Carcass") {
            $category = 'Demi Carcass';
        }
        $resources =$this->blockChainService->getAllRessourceFromWalletAddress($this->getUser()->getWalletAddress(),$category);
        // dd($resources);
        // if ($request->isMethod('POST')) {
        //     try {
        //         $resources = $listHandler->getSpecificResource($request->request->get('NFC'), $this->getUser());
        //     }
        //     catch (\Exception $e) {
        //         $this->addFlash('error', $e->getMessage());
        //         return $this->redirectToRoute('app_usine_list', ['category' => $category] );
        //     }
        // }
        // else{
        //     $resources = $listHandler->getResources($this->getUser(), $category);
        // }

        return $this->render('pro/usine/list.html.twig',
            ['resources' => $resources,
                'category' => $category]
        );

    }


    #[Route('/specific/{id}/{category}', name: 'app_usine_specific')]
    public function specific(ResourceRepository $resourceRepo,
                             $id, $category): Response
    {
        switch ($category) {
            case 'Demi%20Carcass' or 'Demi Carcass':
                $nextCategory = 'Meat';
                break;
            default:
                $nextCategory = "Meat";
                break;
        }
        $resource =$this->blockChainService->getRessourceFromTokenId($id);
        // dd($resource);
        // dd($resource["resourceID"], "MANUFACTURER", $nextCategory);
        $possibleResource = $this->blockChainService->getPossibleResourceFromResourceID($resource["resourceID"], "MANUFACTURER", $nextCategory);
        // dd($possibleResource);
        // $resource = $resourceRepo->find($id);
        // if (!$this->usineHandler->canHaveAccess($resource, $this->getUser())) {
        //     $this->addFlash('error', 'Cette ressource ne vous appartient pas');
        //     return $this->redirectToRoute('app_usine_list', ['category' => 'MORCEAU']);

        // }
        // $category = $resource->getResourceName()->getResourceCategory()->getCategory();
        return $this->render('pro/usine/specific.html.twig', [
            'resource' => $resource,
            'category' => $category
        ]);
    }


    #[Route('/decoupe/{id}', name: 'app_usine_decoupe')]
    public function decoupe(Request $request,
                            ResourceRepository $resourceRepository,
                            ResourceNameRepository $nameRepository,
                            $id): Response
    {
        $demiCarcasse = $resourceRepository->find($id);

        if (!$this->usineHandler->canCutIntoPieces($demiCarcasse, $this->getUser())) {
            $this->addFlash('error', 'Il y a eu une erreur, veuillez réessayer');
            return $this->redirectToRoute('app_usine_list', ['category' => 'DEMI-CARCASSE']);
        }
        $morceaux = $nameRepository->findByCategoryAndFamily(category: 'MORCEAU',
            family: $demiCarcasse->getResourceName()->getResourceFamilies()[0]->getName());
            // Only products can have multiple families

        if ($request->isMethod('POST')) {
            $list = $request->request->all()['list'];
            try {
                $this->usineHandler->cuttingProcess($demiCarcasse, $morceaux, $list, $this->getUser());
                $this->addFlash('success', 'La demi-carcasse a bien été découpée');
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'Au moins un tag NFC est déjà utilisé par une autre ressource');
                return $this->redirectToRoute('app_usine_decoupe', ['id' => $id]);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_usine_decoupe', ['id' => $id]);
            }
            return $this->redirectToRoute('app_usine_list' , ['category' => 'MORCEAU']);
        }

        return $this->render('pro/usine/decoupe.html.twig', [
            'demiCarcasse' => $demiCarcasse, // La demi-carcasse à découper
            'morceauxPossibles' => $morceaux // Les ressources possibles à partir d'elle
        ]);
    }


    #[Route('/creationRecette/name', name: 'app_usine_creationRecetteName')]
    public function creationRecetteName(ResourceFamilyRepository $repoFamily): Response
    {
        $families = $repoFamily->findAll();
        return $this->render('pro/usine/creationRecetteName.html.twig',
        [
            'families' => $families
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
                'ingredients' => $ingredients
            ]);
        }
        return $this->redirectToRoute('app_usine_creationRecetteName');
    }


    #[Route('/creationRecette/process', name: 'app_usine_creationRecetteProcess')]
    public function creationRecetteProcess(Request $request) : RedirectResponse
    {
        if ($request->isMethod('POST')) {
            $list = $request->request->all()['list']; //an Array like [['ingredient' => 'name', 'quantity' => 'quantity'], ...]
            $name = $request->request->get('name');
            try {
                $this->usineHandler->recipeCreatingProcess($list, $name, $this->getUser());
            } catch (\Exception $e){
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_usine_creationRecetteName');
            }
            $this->addFlash('success', 'La recette a bien été enregistrée');
            return $this->redirectToRoute('app_usine_index');
        }
        return $this->redirectToRoute('app_usine_creationRecetteName');
    }


    #[Route('/choixRecette', name: 'app_usine_choixRecette')]
    public function choixRecette(ResourceNameRepository $nameRepo) : Response
    {
        $ownedRecettes = $nameRepo->findBy(['productionSiteOwner' => $this->getUser()->getProductionSite()]);
        return $this->render('pro/usine/choixRecette.html.twig', [
            'titles' => $ownedRecettes
        ]);
    }


    #[Route('/recette/{id}', name: 'app_usine_recette')]
    public function appliRecette($id,
                                 Request $request,
                                 RecipeRepository $recipeRepo,
                                 ResourceNameRepository $nameRepo) : Response
    {
        $ingredients = $recipeRepo->findBy(['recipeTitle' => $id]);
        $recipeTitle = $nameRepo->find($id);

        if ($request -> isMethod('POST')){
            $morceaux = $request->request->all()['morceaux'];
            $newProductId = $request->request->get('newProductId');
            $weight = $request->request->get('weight');
            try {
                $this->usineHandler->recipeApplication($recipeTitle, $ingredients, $morceaux, $this->getUser(),
                    $newProductId, $weight);
            }
            catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_usine_recette', ['id' => $id]);
            }
            $this->addFlash('success', 'La recette a bien été appliquée');
            return $this->redirectToRoute('app_usine_choixRecette');
        }
        return $this->render('pro/usine/appliRecette.html.twig',
            ['ingredients' => $ingredients, 'product' => $recipeTitle]);
    }


    #[Route('/transaction', name: 'app_usine_transferList')]
    public function transferList(OwnershipAcquisitionRequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser() ,'state' => 'En attente']);
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


    #[Route('/transaction/all' , name: 'app_usine_transferAll')]
    public function transferAll(): RedirectResponse
    {
        try {
            $this->transactionHandler->acceptAllTransactions($this->getUser());
            $this->addFlash('success', 'Toutes les transactions ont été effectuées');
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_usine_transferList');
        }
    }

}
