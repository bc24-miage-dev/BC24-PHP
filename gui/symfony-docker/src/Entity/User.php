<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: Report::class)]
    private Collection $reports;

    #[ORM\OneToMany(mappedBy: 'User', targetEntity: UserResearch::class)]
    private Collection $userResearch;

    #[ORM\OneToMany(mappedBy: 'currentOwner', targetEntity: Resource::class)]
    private Collection $ownedResources;

    #[ORM\ManyToOne(inversedBy: 'userRelated')]
    private ?ProductionSite $productionSite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $WalletAddress = null;

    public function __construct()
    {
        $this->reports = new ArrayCollection();
        $this->userResearch = new ArrayCollection();
        $this->ownedResources = new ArrayCollection();
    }

    // #[ORM\Column(type: 'boolean')]
    // private $isVerified = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    // public function isVerified(): bool
    // {
    //     return $this->isVerified;
    // }

    // public function setIsVerified(bool $isVerified): static
    // {
    //     $this->isVerified = $isVerified;

    //     return $this;
    // }

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
            $report->setUser($this);
        }

        return $this;
    }

    public function removeReport(Report $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getUser() === $this) {
                $report->setUser(null);
            }
        }

        return $this;
    }

    public function setSpecificRole(string $newRole) : User
    {
        $newRoles = $newRole != 'ROLE_USER' ? array($newRole, "ROLE_PRO") : array("ROLE_USER");
        $this->setRoles($newRoles);
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
            $userResearch->setUser($this);
        }

        return $this;
    }

    public function removeUserResearch(UserResearch $userResearch): static
    {
        if ($this->userResearch->removeElement($userResearch)) {
            // set the owning side to null (unless already changed)
            if ($userResearch->getUser() === $this) {
                $userResearch->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getOwnedResources(): Collection
    {
        return $this->ownedResources;
    }

    public function addOwnedResources(Resource $ownedResources): static
    {
        if (!$this->ownedResources->contains($ownedResources)) {
            $this->ownedResources->add($ownedResources);
            $ownedResources->setCurrentOwner($this);
        }

        return $this;
    }

    public function removeOwnedResources(Resource $ownedResources): static
    {
        if ($this->ownedResources->removeElement($ownedResources)) {
            // set the owning side to null (unless already changed)
            if ($ownedResources->getCurrentOwner() === $this) {
                $ownedResources->setCurrentOwner(null);
            }
        }

        return $this;
    }

    public function getProductionSite(): ?ProductionSite
    {
        return $this->productionSite;
    }

    public function setProductionSite(?ProductionSite $productionSite): static
    {
        $this->productionSite = $productionSite;

        return $this;
    }

    public function getWalletAddress(): ?string
    {
        return $this->WalletAddress;
    }

    public function setWalletAddress(?string $WalletAddress): static
    {
        $this->WalletAddress = $WalletAddress;

        return $this;
    }
}
