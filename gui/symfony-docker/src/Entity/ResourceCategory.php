<?php

namespace App\Entity;

use App\Repository\ResourceCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceCategoryRepository::class)]
class ResourceCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $category = null;

    #[ORM\OneToMany(mappedBy: 'resourceCategory', targetEntity: ResourceName::class)]
    private Collection $ResourceNamesRelated;

    public function __construct()
    {
        $this->ResourceNamesRelated = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, ResourceName>
     */
    public function getResourceNamesRelated(): Collection
    {
        return $this->ResourceNamesRelated;
    }

    public function addResourceNamesRelated(ResourceName $resourceNamesRelated): static
    {
        if (!$this->ResourceNamesRelated->contains($resourceNamesRelated)) {
            $this->ResourceNamesRelated->add($resourceNamesRelated);
            $resourceNamesRelated->setResourceCategory($this);
        }

        return $this;
    }

    public function removeResourceNamesRelated(ResourceName $resourceNamesRelated): static
    {
        if ($this->ResourceNamesRelated->removeElement($resourceNamesRelated)) {
            // set the owning side to null (unless already changed)
            if ($resourceNamesRelated->getResourceCategory() === $this) {
                $resourceNamesRelated->setResourceCategory(null);
            }
        }

        return $this;
    }
}
