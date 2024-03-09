<?php

namespace App\Entity;

use App\Repository\ProductionSiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductionSiteRepository::class)]
class ProductionSite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $ProductionSiteName = null;

    #[ORM\Column(length: 255)]
    private ?string $Address = null;

    #[ORM\Column(length: 15)]
    private ?string $ProductionSiteTel = null;

    #[ORM\OneToMany(mappedBy: 'origin', targetEntity: Resource::class)]
    private Collection $resources;

    #[ORM\OneToMany(mappedBy: 'FactoryOwner', targetEntity: FactoryRecipe::class)]
    private Collection $factoryRecipes;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
        $this->factoryRecipes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductionSiteName(): ?string
    {
        return $this->ProductionSiteName;
    }

    public function setProductionSiteName(string $ProductionSiteName): static
    {
        $this->ProductionSiteName = $ProductionSiteName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->Address;
    }

    public function setAddress(string $Address): static
    {
        $this->Address = $Address;

        return $this;
    }

    public function getProductionSiteTel(): ?string
    {
        return $this->ProductionSiteTel;
    }

    public function setProductionSiteTel(string $ProductionSiteTel): static
    {
        $this->ProductionSiteTel = $ProductionSiteTel;

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function addResource(Resource $resource): static
    {
        if (!$this->resources->contains($resource)) {
            $this->resources->add($resource);
            $resource->setOrigin($this);
        }

        return $this;
    }

    public function removeResource(Resource $resource): static
    {
        if ($this->resources->removeElement($resource)) {
            // set the owning side to null (unless already changed)
            if ($resource->getOrigin() === $this) {
                $resource->setOrigin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FactoryRecipe>
     */
    public function getFactoryRecipes(): Collection
    {
        return $this->factoryRecipes;
    }

    public function addFactoryRecipe(FactoryRecipe $factoryRecipe): static
    {
        if (!$this->factoryRecipes->contains($factoryRecipe)) {
            $this->factoryRecipes->add($factoryRecipe);
            $factoryRecipe->setFactoryOwner($this);
        }

        return $this;
    }

    public function removeFactoryRecipe(FactoryRecipe $factoryRecipe): static
    {
        if ($this->factoryRecipes->removeElement($factoryRecipe)) {
            // set the owning side to null (unless already changed)
            if ($factoryRecipe->getFactoryOwner() === $this) {
                $factoryRecipe->setFactoryOwner(null);
            }
        }

        return $this;
    }
}
