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
#[UniqueEntity(fields: ['number'], message: 'There is already an account with this number')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $number = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    #[ORM\OneToOne(targetEntity: Hours::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Hours $hour = null;

    #[ORM\OneToOne(targetEntity: UserProfile::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserProfile $userProfile = null;

    #[ORM\OneToMany(targetEntity: OldAppointment::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $oldAppointments;

    #[ORM\OneToOne(mappedBy: 'User', cascade: ['persist', 'remove'])]
    private ?BanList $banList = null;

    public function __construct()
    {
        $this->oldAppointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->number ?? '';
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // Clear sensitive temporary data if needed
    }

    public function getHour(): ?Hours
    {
        return $this->hour;
    }

    public function setHour(?Hours $hour): self
    {
        if ($hour === null && $this->hour !== null) {
            $this->hour->setUser(null);
        }

        if ($hour !== null && $hour->getUser() !== $this) {
            $hour->setUser($this);
        }

        $this->hour = $hour;

        return $this;
    }

    public function getUserProfile(): ?UserProfile
    {
        return $this->userProfile;
    }

    public function setUserProfile(?UserProfile $userProfile): self
    {
        if ($userProfile === null && $this->userProfile !== null) {
            $this->userProfile->setUser(null);
        }

        if ($userProfile !== null && $userProfile->getUser() !== $this) {
            $userProfile->setUser($this);
        }

        $this->userProfile = $userProfile;

        return $this;
    }

    public function getOldAppointments(): Collection
    {
        return $this->oldAppointments;
    }

    public function addOldAppointment(OldAppointment $oldAppointment): self
    {
        if (!$this->oldAppointments->contains($oldAppointment)) {
            $this->oldAppointments->add($oldAppointment);
            // Vérifie si l'utilisateur de l'ancien rendez-vous est déjà défini
            if ($oldAppointment->getUser() !== $this) {
                $oldAppointment->setUser($this);
            }
        }

        return $this;
    }

    public function removeOldAppointment(OldAppointment $oldAppointment): self
    {
        if ($this->oldAppointments->removeElement($oldAppointment)) {
            // Assure que la relation est correctement rompue
            if ($oldAppointment->getUser() === $this) {
                $oldAppointment->setUser(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        $userFirst = $this->getUserProfile()->getFirstName();
        $userLast = $this->getUserProfile()->getLastName();

        return $userLast .' '. $userFirst;
    }

    public function getBanList(): ?BanList
    {
        return $this->banList;
    }

    public function setBanList(BanList $banList): static
    {
        // set the owning side of the relation if necessary
        if ($banList->getUser() !== $this) {
            $banList->setUser($this);
        }

        $this->banList = $banList;

        return $this;
    }
}
