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

    public function __construct()
    {
        $this->ResourcesUsingThisName = new ArrayCollection();
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
}
