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

    #[ORM\ManyToMany(targetEntity: ResourceName::class, mappedBy: 'resourceFamilies')]
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
    public function getResourceNamesUsingThisFamily(): Collection
    {
        return $this->resourceNames;
    }

    public function addResourceNamesUsingThisFamily(ResourceName $resourceNamesUsingThisFamily): static
    {
        if (!$this->resourceNames->contains($resourceNamesUsingThisFamily)) {
            $this->resourceNames->add($resourceNamesUsingThisFamily);
            $resourceNamesUsingThisFamily->addResourceFamily($this);
        }

        return $this;
    }

    public function removeResourceNamesUsingThisFamily(ResourceName $resourceNamesUsingThisFamily): static
    {
        if ($this->resourceNames->removeElement($resourceNamesUsingThisFamily)) {
            $resourceNamesUsingThisFamily->removeResourceFamily($this);
        }

        return $this;
    }

}
