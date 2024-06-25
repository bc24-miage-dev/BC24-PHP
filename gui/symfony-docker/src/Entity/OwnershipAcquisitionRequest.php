<?php

namespace App\Entity;

use App\Repository\OwnershipAcquisitionRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OwnershipAcquisitionRequestRepository::class)]
class OwnershipAcquisitionRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $requestDate = null;

    #[ORM\ManyToOne(inversedBy: 'ownershipAcquisitionRequestsSent')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $requester = null;

    #[ORM\ManyToOne(inversedBy: 'ownershipAcquisitionRequestsReceived')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $initialOwner = null;

    // #[ORM\ManyToOne(inversedBy: 'ownershipAcquisitionRequestsRelated')]
    // #[ORM\JoinColumn(nullable: false)]
    // private ?Resource $resource = null;

    #[ORM\Column(length: 15)]
    private ?string $state = null;

    #[ORM\Column(nullable: true)]
    private ?int $resourceTokenID = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestDate(): ?\DateTimeInterface
    {
        return $this->requestDate;
    }

    public function setRequestDate(\DateTimeInterface $requestDate): static
    {
        $this->requestDate = $requestDate;

        return $this;
    }

    public function getRequester(): ?User
    {
        return $this->requester;
    }

    public function setRequester(?User $requester): static
    {
        $this->requester = $requester;

        return $this;
    }

    public function getInitialOwner(): ?User
    {
        return $this->initialOwner;
    }

    public function setInitialOwner(?User $initialOwner): static
    {
        $this->initialOwner = $initialOwner;

        return $this;
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function setResource(?Resource $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getResourceTokenID(): ?int
    {
        return $this->resourceTokenID;
    }

    public function setResourceTokenID(?int $resourceTokenID): static
    {
        $this->resourceTokenID = $resourceTokenID;

        return $this;
    }
}
