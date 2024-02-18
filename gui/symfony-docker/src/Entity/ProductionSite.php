<?php

namespace App\Entity;

use App\Repository\ProductionSiteRepository;
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
}
