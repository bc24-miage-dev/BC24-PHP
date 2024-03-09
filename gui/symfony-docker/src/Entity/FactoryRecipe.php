<?php

namespace App\Entity;

use App\Repository\FactoryRecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactoryRecipeRepository::class)]
class FactoryRecipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: ResourceName::class)]
    private Collection $ingredients;

    #[ORM\ManyToOne(inversedBy: 'factoryRecipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductionSite $FactoryOwner = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ResourceName $RecipeName = null;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, ResourceName>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(ResourceName $ingredient): static
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
        }

        return $this;
    }

    public function removeIngredient(ResourceName $ingredient): static
    {
        $this->ingredients->removeElement($ingredient);

        return $this;
    }

    public function getFactoryOwner(): ?ProductionSite
    {
        return $this->FactoryOwner;
    }

    public function setFactoryOwner(?ProductionSite $FactoryOwner): static
    {
        $this->FactoryOwner = $FactoryOwner;

        return $this;
    }

    public function getRecipeName(): ?ResourceName
    {
        return $this->RecipeName;
    }

    public function setRecipeName(?ResourceName $RecipeName): static
    {
        $this->RecipeName = $RecipeName;

        return $this;
    }
}
