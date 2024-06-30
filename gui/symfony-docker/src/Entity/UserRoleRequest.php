<?php

namespace App\Entity;

use App\Repository\UserRoleRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRoleRequestRepository::class)]
class UserRoleRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    #[ORM\Column(length: 255)]
    private ?string $roleRequest = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateRoleRequest = null;

    #[ORM\Column(nullable: true)]
    private ?bool $Readed = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Description = null;

    #[ORM\ManyToOne(inversedBy: 'userRoleRequests')]
    private ?ProductionSite $ProductionSite = null;

    #[ORM\Column(length: 255 , nullable: true)]
    private ?string $WalletAddress = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getRoleRequest(): ?string
    {
        return $this->roleRequest;
    }

    public function setRoleRequest(string $roleRequest): static
    {
        $this->roleRequest = $roleRequest;

        return $this;
    }

    public function getDateRoleRequest(): ?\DateTimeInterface
    {
        return $this->dateRoleRequest;
    }

    public function setDateRoleRequest(\DateTimeInterface $dateRoleRequest): static
    {
        $this->dateRoleRequest = $dateRoleRequest;

        return $this;
    }

    public function isReaded(): ?bool
    {
        return $this->Readed;
    }

    public function setReaded(bool $Readed): static
    {
        $this->Readed = $Readed;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getProductionSite(): ?ProductionSite
    {
        return $this->ProductionSite;
    }

    public function setProductionSite(?ProductionSite $ProductionSite): static
    {
        $this->ProductionSite = $ProductionSite;

        return $this;
    }

    public function getWalletAddress(): ?string
    {
        return $this->WalletAddress;
    }

    public function setWalletAddress(string $WalletAddress): static
    {
        $this->WalletAddress = $WalletAddress;

        return $this;
    }
    
}
