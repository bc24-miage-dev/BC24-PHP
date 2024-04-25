<?php

namespace App\Handlers;

use App\Entity\ProductionSite;
use App\Entity\Recipe;
use App\Entity\Resource;
use App\Entity\ResourceName;
use App\Repository\ResourceCategoryRepository;
use App\Repository\ResourceNameRepository;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SQLiteException;
use Symfony\Component\Security\Core\User\UserInterface;

class UsineHandler extends ProHandler
{
    private ResourceNameRepository $nameRepository;
    private ResourceCategoryRepository $categoryRepository;
    private ResourceHandler $resourceHandler;
    private ResourceNameHandler $nameHandler;

    public function __construct(EntityManagerInterface $em,
                                ResourceNameRepository $nameRepository,
                                ResourceHandler $resourceHandler,
                                ResourceNameHandler $nameHandler,
                                ResourceCategoryRepository $categoryRepository,
                                ResourceRepository $resourceRepository)
    {
        parent::__construct($em, $resourceRepository);
        $this->nameRepository = $nameRepository;
        $this->resourceHandler = $resourceHandler;
        $this->nameHandler = $nameHandler;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @throws Exception
     */
    public function cuttingProcess(Resource $demiCarcasse,
                                   array $morceaux, array $listOfPieces,
                                   UserInterface $user): void
    {
        if (! $this->checkNotMultipleIdToCut($listOfPieces)) {
            throw new Exception('Vous ne pouvez pas utiliser le même NFC pour plusieurs morceaux');
        }
        foreach ($listOfPieces as $element) {
            $childResource = $this->resourceHandler->createChildResource($demiCarcasse, $user);
            $childResource->setWeight($element['weight']);
            $childResource->setId($element['NFC']);
            $childResource->setResourceName($this->searchInArrayByName($morceaux, $element['name']));
            $this->entityManager->persist($childResource);
        }
        $demiCarcasse->setIsLifeCycleOver(true);
        $this->entityManager->persist($demiCarcasse);
        $this->entityManager->flush();
    }

    private function checkNotMultipleIdToCut(array $listOfPieces) : bool
    {
        $nfcs = [];
        foreach ($listOfPieces as $element) {
            if (in_array($element['NFC'], $nfcs)) {
                return false;
            }
            $nfcs[] = $element['NFC'];
        }
        return true;
    }

    public function canCutIntoPieces(?Resource $resource, UserInterface $user) : bool
    {
        return parent::canHaveAccess($resource, $user) &&
            $resource->getResourceName()->getResourceCategory()->getCategory() == 'DEMI-CARCASSE';
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

    public function getAllPossibleIngredients(array $families) : array
    {
        $ingredients = [];
        foreach($families as $family){
            foreach($this->nameRepository->findByCategoryAndFamily('MORCEAU', $family) as $ingredient){
                $ingredients[] = $ingredient;
            }
        }
        return $ingredients;
    }

    /**
     * @throws Exception
     */
    public function recipeCreatingProcess(array $list, String $name, UserInterface $user) : void
    {
        if (! $this->checkNotMultipleIngredients($list)) {
            throw new Exception('Vous ne pouvez pas utiliser le même ingrédient plusieurs fois, spécifiez plutôt sa quantité');
        }
        $actualFamilies = [];
        $newProduct = $this->nameHandler->createResourceName(
            $name,
            $this->categoryRepository->findOneBy(['category' => 'PRODUIT']),
            $user->getProductionSite()
        );

        foreach ($list as $element) {
            $recipe = new Recipe();
            $recipe->setIngredient($this->nameRepository->findOneBy(['name' => $element['ingredient']]));
            $recipe->setIngredientNumber(intval($element['quantity']) > 0 ? intval($element['quantity']) : 1);
            $recipe->setRecipeTitle($newProduct);

            if (! isset($actualFamilies[$recipe->getIngredient()->getResourceFamilies()[0]->getName()])) {
                // Set simulation; get a set of ResourceFamilies used in the recipe
                $actualFamilies[$recipe->getIngredient()->getResourceFamilies()[0]->getName()] = $recipe->getIngredient()->getResourceFamilies()[0];
            }
            $this->entityManager->persist($recipe);
        }
        $newProduct->setResourceFamilies($actualFamilies);
        $this->entityManager->persist($newProduct);
        $this->entityManager->flush();
    }

    private function checkNotMultipleIngredients(array $list) : bool
    {
        $ingredients = [];
        foreach ($list as $element) {
            if (in_array($element['ingredient'], $ingredients)) {
                return false;
            }
            $ingredients[] = $element['ingredient'];
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function recipeApplication(ResourceName $recipeTitle,
                                      array $neededIngredients,
                                      array $providedIngredients,
                                      UserInterface $user,
                                      int $newProductId,
                                      int $newProductWeight) : void
    {
        if ($this->ingredientMoreThanOnceInArray($providedIngredients)) {
            throw new Exception('Vous ne pouvez pas utilisez le même ingrédient plusieurs fois');
        }
        $i = 0;
        foreach ($neededIngredients as $neededIngredient) { //Test loop to check if the morceaux match with the recipe
            for ($j = 0; $j < $neededIngredient->getIngredientNumber(); $j++) {
                $resource = $this->resourceRepository->find($providedIngredients[$i]);
                if ((!parent::canHaveAccess($resource, $user)) ||
                    $resource->getResourceName()->getName() != $neededIngredient->getIngredient()->getName()) {
                    throw new Exception('Erreur dans la sélection des morceaux');
                }
                $i++;
            }
        }
        $newProduct = $this->resourceHandler->createDefaultNewResource($user);
        $newProduct->setId($newProductId);
        $newProduct->setResourceName($recipeTitle);
        $newProduct->setWeight($newProductWeight);
        try {
            $this->entityManager->persist($newProduct);
            $this->entityManager->flush();
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new Exception('Cet identifiant est déjà utilisé pour un autre produit');
        }
        foreach ($providedIngredients as $morceau) {
            $resource = $this->resourceRepository->find($morceau);
            $resource->setIsLifeCycleOver(true);
            $resource->addResource($newProduct);
            $this->entityManager->persist($resource);
            $this->entityManager->flush();
        }
    }

    private function ingredientMoreThanOnceInArray(array $ingredientsList) : bool
    {
        $verifiedIngredients = [];
        foreach ($ingredientsList as $ingredient) {
            if (in_array($ingredient, $verifiedIngredients)) {
                return true;
            }
            $verifiedIngredients[] = $ingredient;
        }
        return false;
    }

    public function nameAlreadyExists(String $name, ProductionSite $productionSite) : bool
    {
        return $this->nameRepository->findOneBy(['name' => $name, 'productionSiteOwner' => $productionSite]) != null;
    }
}
