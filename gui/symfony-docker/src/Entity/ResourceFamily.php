<?php

namespace App\Entity;

use App\Repository\ResourceFamilyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceFamilyRepository::class)]
class ResourceFamily
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'family', targetEntity: ResourceName::class)]
    private Collection $resourceNames;

    public function __construct()
    {
        $this->resourceNames = new ArrayCollection();
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
     * @return Collection<int, ResourceName>
     */
    public function getResourceNames(): Collection
    {
        return $this->resourceNames;
    }

    public function addResourceName(ResourceName $resourceName): static
    {
        if (!$this->resourceNames->contains($resourceName)) {
            $this->resourceNames->add($resourceName);
            $resourceName->setFamily($this);
        }

        return $this;
    }

    public function removeResourceName(ResourceName $resourceName): static
    {
        if ($this->resourceNames->removeElement($resourceName)) {
            // set the owning side to null (unless already changed)
            if ($resourceName->getFamily() === $this) {
                $resourceName->setFamily(null);
            }
        }

        return $this;
    }
}
