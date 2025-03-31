<?php

namespace App\Entity;

use App\Repository\HoursRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HoursRepository::class)]
class Hours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'time')]
    #[Assert\NotBlank(message: 'L\'heure ne doit pas être vide.')]
    #[Assert\Time(message: 'L\'heure doit être valide.')]
    private ?\DateTimeInterface $hour = null;

    #[ORM\ManyToOne(targetEntity: Date::class, inversedBy: 'hours')]
    #[Assert\NotNull(message: 'La date ne doit pas être nulle.')]
    #[Assert\Valid]
    private ?Date $date = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'hour', cascade: ['persist'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Prestations::class, inversedBy: 'hours')]
    private ?Prestations $prestation = null;

    #[ORM\ManyToOne(targetEntity: Variant::class)]
    #[ORM\JoinColumn(nullable: true)] // La variante est optionnelle
    private ?Variant $variant = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $cancelToken = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHour(): ?\DateTimeInterface
    {
        return $this->hour;
    }

    public function setHour(\DateTimeInterface $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    public function getDate(): ?Date
    {
        return $this->date;
    }

    public function setDate(?Date $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPrestation(): ?Prestations
    {
        return $this->prestation;
    }

    public function setPrestation(?Prestations $prestation): self
    {
        $this->prestation = $prestation;

        return $this;
    }

    public function getVariant(): ?Variant
    {
        return $this->variant;
    }

    public function setVariant(?Variant $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function getCancelToken(): ?string
    {
        return $this->cancelToken;
    }

    public function setCancelToken(?string $cancelToken): self
    {
        $this->cancelToken = $cancelToken;

        return $this;
    }

    public function __toString(): string
    {
        return $this->hour ? $this->hour->format('H:i') : 'Heure non définie';
    }
}
