<?php

namespace App\Entity;

use App\Repository\ResourceNameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceNameRepository::class)]
class ResourceName
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'ResourceName', targetEntity: Resource::class)]
    private Collection $ResourcesUsingThisName;

    #[ORM\ManyToOne(inversedBy: 'ResourceNamesRelated')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ResourceCategory $resourceCategory = null;

    #[ORM\ManyToOne(inversedBy: 'resourceNames')]
    private ?ResourceFamily $family = null;

    #[ORM\ManyToOne(inversedBy: 'resourceNamesOwned')]
    private ?ProductionSite $productionSiteOwner = null;

    #[ORM\OneToMany(mappedBy: 'recipeTitle', targetEntity: Recipe::class)]
    private Collection $recipes;

    #[ORM\OneToMany(mappedBy: 'ingredient', targetEntity: Recipe::class)]
    private Collection $recipesThisNameIsIngredient;

    public function __construct()
    {
        $this->ResourcesUsingThisName = new ArrayCollection();
        $this->recipes = new ArrayCollection();
        $this->recipesThisNameIsIngredient = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getResourcesUsingThisName(): Collection
    {
        return $this->ResourcesUsingThisName;
    }

    public function addResourcesUsingThisName(Resource $resourcesUsingThisName): static
    {
        if (!$this->ResourcesUsingThisName->contains($resourcesUsingThisName)) {
            $this->ResourcesUsingThisName->add($resourcesUsingThisName);
            $resourcesUsingThisName->setResourceName($this);
        }

        return $this;
    }

    public function removeResourcesUsingThisName(Resource $resourcesUsingThisName): static
    {
        if ($this->ResourcesUsingThisName->removeElement($resourcesUsingThisName)) {
            // set the owning side to null (unless already changed)
            if ($resourcesUsingThisName->getResourceName() === $this) {
                $resourcesUsingThisName->setResourceName(null);
            }
        }

        return $this;
    }

    public function getResourceCategory(): ?ResourceCategory
    {
        return $this->resourceCategory;
    }

    public function setResourceCategory(?ResourceCategory $resourceCategory): static
    {
        $this->resourceCategory = $resourceCategory;

        return $this;
    }

    public function getFamily(): ?ResourceFamily
    {
        return $this->family;
    }

    public function setFamily(?ResourceFamily $family): static
    {
        $this->family = $family;

        return $this;
    }

    public function getProductionSiteOwner(): ?ProductionSite
    {
        return $this->productionSiteOwner;
    }

    public function setProductionSiteOwner(?ProductionSite $productionSiteOwner): static
    {
        $this->productionSiteOwner = $productionSiteOwner;

        return $this;
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): static
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes->add($recipe);
            $recipe->setRecipeTitle($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        if ($this->recipes->removeElement($recipe)) {
            // set the owning side to null (unless already changed)
            if ($recipe->getRecipeTitle() === $this) {
                $recipe->setRecipeTitle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipesThisNameIsIngredient(): Collection
    {
        return $this->recipesThisNameIsIngredient;
    }

    public function addRecipesThisNameIsIngredient(Recipe $recipesThisNameIsIngredient): static
    {
        if (!$this->recipesThisNameIsIngredient->contains($recipesThisNameIsIngredient)) {
            $this->recipesThisNameIsIngredient->add($recipesThisNameIsIngredient);
            $recipesThisNameIsIngredient->setIngredient($this);
        }

        return $this;
    }

    public function removeRecipesThisNameIsIngredient(Recipe $recipesThisNameIsIngredient): static
    {
        if ($this->recipesThisNameIsIngredient->removeElement($recipesThisNameIsIngredient)) {
            // set the owning side to null (unless already changed)
            if ($recipesThisNameIsIngredient->getIngredient() === $this) {
                $recipesThisNameIsIngredient->setIngredient(null);
            }
        }

        return $this;
    }

}
