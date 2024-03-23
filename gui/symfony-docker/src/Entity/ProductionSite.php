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
    #[ORM\OneToMany(mappedBy: 'productionSite', targetEntity: User::class)]
    private Collection $userRelated;

    #[ORM\OneToMany(mappedBy: 'ProductionSite', targetEntity: UserRoleRequest::class)]
    private Collection $userRoleRequests;

    #[ORM\Column(nullable: true)]
    private ?bool $Validate = false;

    #[ORM\OneToMany(mappedBy: 'productionSiteOwner', targetEntity: ResourceName::class)]
    private Collection $resourceNamesOwned;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
        $this->userRelated = new ArrayCollection();
        $this->userRoleRequests = new ArrayCollection();
        $this->resourceNamesOwned = new ArrayCollection();
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
     * @return Collection<int, User>
     */
    public function getUserRelated(): Collection
    {
        return $this->userRelated;
    }

    public function addUserRelated(User $userRelated): static
    {
        if (!$this->userRelated->contains($userRelated)) {
            $this->userRelated->add($userRelated);
            $userRelated->setProductionSite($this);
        }

        return $this;
    }

    public function removeUserRelated(User $userRelated): static
    {
        if ($this->userRelated->removeElement($userRelated)) {
            // set the owning side to null (unless already changed)
            if ($userRelated->getProductionSite() === $this) {
                $userRelated->setProductionSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserRoleRequest>
     */
    public function getUserRoleRequests(): Collection
    {
        return $this->userRoleRequests;
    }

    public function addUserRoleRequest(UserRoleRequest $userRoleRequest): static
    {
        if (!$this->userRoleRequests->contains($userRoleRequest)) {
            $this->userRoleRequests->add($userRoleRequest);
            $userRoleRequest->setProductionSite($this);
        }

        return $this;
    }

    public function removeUserRoleRequest(UserRoleRequest $userRoleRequest): static
    {
        if ($this->userRoleRequests->removeElement($userRoleRequest)) {
            // set the owning side to null (unless already changed)
            if ($userRoleRequest->getProductionSite() === $this) {
                $userRoleRequest->setProductionSite(null);
            }
        }

        return $this;
    }

    public function isValidate(): ?bool
    {
        return $this->Validate;
    }

    public function setValidate(?bool $Validate): static
    {
        $this->Validate = $Validate;

        return $this;
    }

    /**
     * @return Collection<int, ResourceName>
     */
    public function getResourceNamesOwned(): Collection
    {
        return $this->resourceNamesOwned;
    }

    public function addResourceNamesOwned(ResourceName $resourceNamesOwned): static
    {
        if (!$this->resourceNamesOwned->contains($resourceNamesOwned)) {
            $this->resourceNamesOwned->add($resourceNamesOwned);
            $resourceNamesOwned->setProductionSiteOwner($this);
        }

        return $this;
    }

    public function removeResourceNamesOwned(ResourceName $resourceNamesOwned): static
    {
        if ($this->resourceNamesOwned->removeElement($resourceNamesOwned)) {
            // set the owning side to null (unless already changed)
            if ($resourceNamesOwned->getProductionSiteOwner() === $this) {
                $resourceNamesOwned->setProductionSiteOwner(null);
            }
        }

        return $this;
    }
}
