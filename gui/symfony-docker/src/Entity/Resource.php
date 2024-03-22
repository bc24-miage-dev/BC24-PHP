<?php

namespace App\Entity;

use App\Repository\ResourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;

#[ORM\Entity(repositoryClass: ResourceRepository::class)]
class Resource
{
    #[ORM\Id]
    //#[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isContamined = null;

    #[ORM\Column]
    private ?float $weight = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'resources')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductionSite $origin = null;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'resources')]
    private Collection $components;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'components')]
    private Collection $resources;

    #[ORM\OneToMany(mappedBy: 'Resource', targetEntity: Report::class)]
    private Collection $reports;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\OneToMany(mappedBy: 'Resource', targetEntity: UserResearch::class)]
    private Collection $userResearch;

    #[ORM\ManyToOne(inversedBy: 'ownedResources')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $currentOwner = null;

    #[ORM\Column]
    private ?bool $IsLifeCycleOver = null;

    #[ORM\ManyToOne(inversedBy: 'ResourcesUsingThisName')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ResourceName $ResourceName = null;

    public function __construct()
    {
        $this->components = new ArrayCollection();
        $this->resources = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->userResearch = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function isIsContamined(): ?bool
    {
        return $this->isContamined;
    }

    public function setIsContamined(bool $isContamined): static
    {
        $this->isContamined = $isContamined;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getOrigin(): ?ProductionSite
    {
        return $this->origin;
    }

    public function setOrigin(?ProductionSite $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getComponents(): Collection
    {
        return $this->components;
    }

    public function addComponent(self $component): static
    {
        if (!$this->components->contains($component)) {
            $this->components->add($component);
        }

        return $this;
    }

    public function removeComponent(self $component): static
    {
        $this->components->removeElement($component);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function addResource(self $resource): static
    {
        if (!$this->resources->contains($resource)) {
            $this->resources->add($resource);
            $resource->addComponent($this);
        }

        return $this;
    }

    public function removeResource(self $resource): static
    {
        if ($this->resources->removeElement($resource)) {
            $resource->removeComponent($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setResource($this);
        }

        return $this;
    }

    public function removeReport(Report $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getResource() === $this) {
                $report->setResource(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, UserResearch>
     */
    public function getUserResearch(): Collection
    {
        return $this->userResearch;
    }

    public function addUserResearch(UserResearch $userResearch): static
    {
        if (!$this->userResearch->contains($userResearch)) {
            $this->userResearch->add($userResearch);
            $userResearch->setResource($this);
        }

        return $this;
    }

    public function removeUserResearch(UserResearch $userResearch): static
    {
        if ($this->userResearch->removeElement($userResearch)) {
            // set the owning side to null (unless already changed)
            if ($userResearch->getResource() === $this) {
                $userResearch->setResource(null);
            }
        }

        return $this;
    }

    public function getCurrentOwner(): ?User
    {
        return $this->currentOwner;
    }

    public function setCurrentOwner(?User $currentOwner): static
    {
        $this->currentOwner = $currentOwner;

        return $this;
    }

    public function isIsLifeCycleOver(): ?bool
    {
        return $this->IsLifeCycleOver;
    }

    public function setIsLifeCycleOver(bool $IsLifeCycleOver): static
    {
        $this->IsLifeCycleOver = $IsLifeCycleOver;

        return $this;
    }

    public function getResourceName(): ?ResourceName
    {
        return $this->ResourceName;
    }

    public function setResourceName(?ResourceName $name): static
    {
        $this->ResourceName = $name;

        return $this;
    }

}
