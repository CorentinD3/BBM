<?php

namespace App\Entity;

use App\Repository\VariantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VariantRepository::class)]
class Variant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $duration = null;

    #[ORM\Column(type: 'float')]
    private ?float $price = null;

    #[ORM\ManyToOne(targetEntity: Prestations::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prestations $prestation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getDuration(): ?\DateTimeInterface
    {
        return $this->duration;
    }

    public function setDuration(\DateTimeInterface $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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

    public function getDurationInMinutes(): int
    {
        if ($this->duration instanceof \DateTimeInterface) {
            return ((int)$this->duration->format('H') * 60) + (int)$this->duration->format('i');
        }

        throw new \InvalidArgumentException('La durée doit être de type DateTimeInterface.');
    }

    public function __toString(): string
    {
        return $this->getLabel();
    }
}
