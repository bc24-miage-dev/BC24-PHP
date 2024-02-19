<?php

namespace App\Entity;

use App\Repository\HistoricRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoricRepository::class)]
class Historic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historics')]
    private ?User $IdUser = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $DateHistoric = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUser(): ?User
    {
        return $this->IdUser;
    }

    public function setIdUser(?User $IdUser): static
    {
        $this->IdUser = $IdUser;

        return $this;
    }

    public function getDateHistoric(): ?\DateTimeInterface
    {
        return $this->DateHistoric;
    }

    public function setDateHistoric(\DateTimeInterface $DateHistoric): static
    {
        $this->DateHistoric = $DateHistoric;

        return $this;
    }
}
