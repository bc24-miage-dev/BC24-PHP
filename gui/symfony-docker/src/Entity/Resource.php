<?php

namespace App\Entity;

use App\Repository\ResourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceRepository::class)]
class Resource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $ResourceName = null;

    #[ORM\Column]
    private ?bool $isFinalProduct = null;

    #[ORM\Column]
    private ?bool $isContamined = null;

    #[ORM\Column]
    private ?float $weight = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResourceName(): ?string
    {
        return $this->ResourceName;
    }

    public function setResourceName(string $ResourceName): static
    {
        $this->ResourceName = $ResourceName;

        return $this;
    }

    public function isIsFinalProduct(): ?bool
    {
        return $this->isFinalProduct;
    }

    public function setIsFinalProduct(bool $isFinalProduct): static
    {
        $this->isFinalProduct = $isFinalProduct;

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
}
