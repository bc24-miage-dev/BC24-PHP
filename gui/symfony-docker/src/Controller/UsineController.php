<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\ResourceName;
use App\Form\ResourceOwnerChangerType;
use App\Handlers\OwnershipHandler;
use App\Handlers\ResourceHandler;
use App\Handlers\ResourceNameHandler;
use App\Repository\OwnershipAcquisitionRequestRepository;
use App\Repository\RecipeRepository;
use App\Repository\ResourceCategoryRepository;
use App\Repository\ResourceFamilyRepository;
use App\Repository\ResourceNameRepository;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pro/usine')]
class UsineController extends AbstractController
{
    #[Route('/', name: 'app_usine_index')]
    public function index(): Response
    {
        return $this->render('pro/usine/index.html.twig');
    }

    #[Route('/arrivage', name:'app_usine_acquire')]
    public function acquire(Request $request,
                            ResourceRepository $resourceRepo,
                            OwnershipAcquisitionRequestRepository $ownershipRepo,
                            EntityManagerInterface $entityManager,
                            OwnershipHandler $ownershipHandler): Response
    {
        $form = $this->createForm(ResourceOwnerChangerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resource =$resourceRepo->find($form->getData()->getId());
            if (!$resource || $resource->getCurrentOwner()->getWalletAddress() == $this->getUser()->getWalletAddress()) {
                $this->addFlash('error', 'Vous ne pouvez pas demander la propriété de cette ressource');
                return $this->redirectToRoute('app_usine_acquire');
            }
            if ($ownershipRepo->findOneBy(['requester' => $this->getUser(), 'resource' => $resource, 'state' => 'En attente'])){
                $this->addFlash('error', 'Vous avez déjà demandé la propriété de cette ressource');
                return $this->redirectToRoute('app_usine_acquire');
            }

            $ownershipHandler->ownershipRequestCreate($this->getUser(), $entityManager, $resource);
            $this->addFlash('success', 'La demande de propriété a bien été envoyée');
            return $this->redirectToRoute('app_usine_acquire');
        }

        $requests = $ownershipRepo->findBy(['requester' => $this->getUser()], ['requestDate' => 'DESC'], limit: 30);
        return $this->render('pro/usine/acquire.html.twig', [
            'form' => $form->createView(),
            'requests' => $requests
        ]);
    }

    #[Route('/list/{category}', name: 'app_usine_list')]
    public function list(ResourceRepository $resourceRepo, Request $request, $category): Response
    {
        if ($request->isMethod('POST')) {
            $NFC = $request->request->get('NFC');
            $resources = $resourceRepo->findByWalletAddressAndNFC($this->getUser()->getWalletAddress(),$NFC);
            if($resources == null){
                $this->addFlash('error', 'Cette ressoure ne vous appartient pas');
                return $this->redirectToRoute('app_usine_index');
            }
        }
        else if ($category == "produit"){
            $resources = $resourceRepo->findProductByWalletAddress($this->getUser()->getWalletAddress());
        }

        else{
        $resources = $resourceRepo->findByWalletAddressCategory($this->getUser()->getWalletAddress(), $category);
        }

        return $this->render('pro/usine/list.html.twig', [
            'resources' => $resources
        ]);
    }

    #[Route('/specific/{id}', name: 'app_usine_specific')]
    public function specific(ResourceRepository $resourceRepo,
                             $id): Response
    {
        $resource = $resourceRepo->find($id);
        if (!$resource || $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress()){
            $this->addFlash('error', 'Cette ressource ne vous appartient pas');
            return $this->redirectToRoute('app_usine_list');
        }
        $category = $resource->getResourceName()->getResourceCategory()->getCategory();
        return $this->render('pro/usine/specific.html.twig', [
            'resource' => $resource,
            'category' => $category
        ]);
    }

    #[Route('/decoupe/{id}', name: 'app_usine_decoupe')]
    public function decoupe(Request $request,
                            EntityManagerInterface $entityManager,
                            ResourceRepository $resourceRepository,
                            ResourceNameRepository $nameRepository,
                            $id): Response
    {
        $resource = $resourceRepository->find($id);

        if (!$resource || $resource->getCurrentOwner()->getWalletAddress() != $this->getUser()->getWalletAddress() ||
            $resource->getResourceName()->getResourceCategory()->getCategory() != 'DEMI-CARCASSE') {
            $this->addFlash('error', 'Ce tag NFC ne correspond pas à une demi-carcasse');
            return $this->redirectToRoute('app_usine_list');
        }

        $resources = $nameRepository->findByCategoryAndFamily(category: 'MORCEAU',
            family: $resourceRepository->find($id)->getResourceName()->getResourceFamilies()[0]->getName());
            // Only products can have multiple families

        if ($request->isMethod('POST')) {
            $list = $request->request->all()['list'];
            $handler = new ResourceHandler();
            foreach ($list as $element) {
                $childResource = $handler->createChildResource($resource, $this->getUser());
                $childResource->setWeight($element['weight']);
                $childResource->setId($element['NFC']);
                $childResource->setResourceName($this->searchInArrayByName($resources, $element['name']));
                $entityManager->persist($childResource);
                $entityManager->flush();
            }
            $resource->setIsLifeCycleOver(true);
            $entityManager->persist($resource);
            $entityManager->flush();

            $this->addFlash('success', 'La demi-carcasse a bien été découpée');
            return $this->redirectToRoute('app_usine_list' , ['category' => 'MORCEAU']);
        }

        return $this->render('pro/usine/decoupe.html.twig', [
            'demiCarcasse' => $resource, // La demi-carcasse à découper
            'morceauxPossibles' => $resources // Les ressources possibles à partir d'elle
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
    public function creationRecetteIngredients(Request $request,
                                              ResourceNameRepository $nameRepo): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $families = $request->request->all()['families'];
            $ingredients = [];
            foreach($families as $family){
                foreach($nameRepo->findByCategoryAndFamily('MORCEAU', $family) as $ingredient){
                    $ingredients[] = $ingredient;
                }
            }
            return $this->render('pro/usine/creationRecetteIngredients.html.twig', [
                'name' => $name,
                'ingredients' => $ingredients
            ]);
        }
        return $this->redirectToRoute('app_usine_creationRecetteName');
    }

    #[Route('/creationRecette/process', name: 'app_usine_creationRecetteProcess')]
    public function creationRecetteProcess(Request $request,
                                           EntityManagerInterface $entityManager,
                                           ResourceFamilyRepository $familyRepo,
                                           ResourceCategoryRepository $categoryRepo,
                                           ResourceNameRepository $nameRepo, ResourceNameHandler $nameHandler) : RedirectResponse
    {
        if ($request->isMethod('POST')) {
            $list = $request->request->all()['list']; //an Array like [['ingredient' => 'name', 'quantity' => 'quantity'], ...]
            $name = $request->request->get('name');
            $families = [];

            $newProduct = $nameHandler->createResourceName(
                $name,
                $categoryRepo->findOneBy(['category' => 'PRODUIT']),
                $this->getUser()->getProductionSite()
            );
            foreach ($list as $element) {
                $recipe = new Recipe();
                $recipe->setIngredient($nameRepo->findOneBy(['name' => $element['ingredient']]));
                $recipe->setIngredientNumber(intval($element['quantity']));
                $recipe->setRecipeTitle($newProduct);

                if (! isset($families[$recipe->getIngredient()->getResourceFamilies()[0]->getName()])) {
                    // Set simulation; get a set of ResourceFamilies used in the recipe
                    $families[$recipe->getIngredient()->getResourceFamilies()[0]->getName()] = $recipe->getIngredient()->getResourceFamilies()[0];
                }
                $entityManager->persist($recipe);
            }
            $newProduct->setResourceFamilies($families);
            $entityManager->persist($newProduct);
            $entityManager->flush();

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
                                 EntityManagerInterface $entityManager,
                                 ResourceRepository $resourceRepo,
                                 RecipeRepository $recipeRepo,
                                 ResourceNameRepository $nameRepo) : Response
    {
        $ingredients = $recipeRepo->findBy(['recipeTitle' => $id]);

        if ($request -> isMethod('POST')){
            $morceaux = $request->request->all()['morceaux'];
            $i = 0;
            foreach ($ingredients as $ingredient){ //Test loop to check if the morceaux match with the recipe
                for ($j = 0; $j<$ingredient->getIngredientNumber(); $j++){
                    $resource = $resourceRepo->find($morceaux[$i]);
                    if ($resource == null || $resource->getResourceName()->getName() != $ingredient->getIngredient()->getName()){
                        $this->addFlash('error', 'Erreur dans la sélection des morceaux');
                        return $this->redirectToRoute('app_usine_recette', ['id' => $id]);
                    }
                    $i++;
                }
            }
            $handler = new ResourceHandler();
            $newProduct = $handler->createDefaultNewResource($this->getUser());
            $newProduct->setId($request->request->get('newProductId'));
            $newProduct->setResourceName($nameRepo->find($id));
            $newProduct->setWeight($request->request->get('weight'));
            $entityManager->persist($newProduct);
            $entityManager->flush();
            foreach ($morceaux as $morceau){
                $resource = $resourceRepo->find($morceau);
                $resource->setIsLifeCycleOver(true);
                $resource->addResource($newProduct);
                $entityManager->persist($resource);
                $entityManager->flush();
            }
            $this->addFlash('success', 'La recette a bien été appliquée');
            return $this->redirectToRoute('app_usine_choixRecette');
        }
        return $this->render('pro/usine/appliRecette.html.twig',
            [
                'ingredients' => $ingredients,
                'product' => $nameRepo->find($id)
            ]);
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
    public function transfer($id,
                             OwnershipAcquisitionRequestRepository $requestRepository,
                             EntityManagerInterface $entityManager ): RedirectResponse
    {
        $request = $requestRepository->find($id);
        if (!$request || $request->getInitialOwner() != $this->getUser()){
            $this->addFlash('error', 'Erreur lors de la transaction');
            return $this->redirectToRoute('app_usine_transferList');
        }
        $resource = $request->getResource();
        $resource->setCurrentOwner($request->getRequester());
        $request->setState('Validé');
        $entityManager->persist($resource);
        $entityManager->persist($request);
        $entityManager->flush();
        $this->addFlash('success', 'Transaction effectuée');

        return $this->redirectToRoute('app_usine_transferList');
    }

    #[Route('/transactionRefused/{id}', name: 'app_usine_transferRefused', requirements: ['id' => '\d+'])]
    public function transferRefused($id,
                                    OwnershipAcquisitionRequestRepository $requestRepository,
                                    EntityManagerInterface $entityManager ): RedirectResponse
    {
        $request = $requestRepository->find($id);
        if (!$request || $request->getInitialOwner() != $this->getUser()){
            $this->addFlash('error', 'Erreur lors de la transaction');
            return $this->redirectToRoute('app_usine_transferList');
        }
        $request->setState('Refusé');
        $entityManager->persist($request);
        $entityManager->flush();
        $this->addFlash('success', 'Transaction refusée avec succès');

        return $this->redirectToRoute('app_usine_transferList');
    }

    #[Route('/transaction/all' , name: 'app_usine_transferAll')]
    public function transferAll(OwnershipAcquisitionRequestRepository $requestRepository,
                                EntityManagerInterface $entityManager): RedirectResponse
    {
        $requests = $requestRepository->findBy(['initialOwner' => $this->getUser() , 'state' => 'En attente']);
        if (!$requests){
            $this->addFlash('error', 'Il n\'y a pas de transaction à effectuer');
            return $this->redirectToRoute('app_usine_transferList');
        }
        foreach ($requests as $request){
            $resource = $request->getResource();
            $resource->setCurrentOwner($request->getRequester());
            $request->setState('Validé');
            $entityManager->persist($resource);
            $entityManager->persist($request);
        }
        $entityManager->flush();
        $this->addFlash('success', 'Toutes les transactions ont été effectuées');

        return $this->redirectToRoute('app_usine_transferList');
    }

    private function searchInArrayByName($array, $nameString): ?ResourceName
    {
        foreach ($array as $item) {
            if ($item->getName() == $nameString) {
                return $item;
            }
        }
        return null;
    }

}
