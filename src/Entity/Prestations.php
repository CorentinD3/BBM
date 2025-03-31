<?php

namespace App\Entity;

use App\Repository\PrestationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrestationsRepository::class)]
class Prestations
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

    #[ORM\OneToMany(targetEntity: Hours::class, mappedBy: 'prestation')]
    private Collection $hours;

    #[ORM\OneToMany(targetEntity: Variant::class, mappedBy: 'prestation', cascade: ['persist', 'remove'])]
    private Collection $variants;

    public function __construct()
    {
        $this->hours = new ArrayCollection();
        $this->variants = new ArrayCollection();
    }

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

    public function __toString(): string
    {
        return $this->label ?? 'Label non défini';
    }

    /**
     * @return Collection<int, Hours>
     */
    public function getHours(): Collection
    {
        return $this->hours;
    }

    public function addHour(Hours $hour): self
    {
        if (!$this->hours->contains($hour)) {
            $this->hours->add($hour);
            $hour->setPrestation($this);
        }

        return $this;
    }

    public function removeHour(Hours $hour): self
    {
        if ($this->hours->removeElement($hour) && $hour->getPrestation() === $this) {
            $hour->setPrestation(null);
        }

        return $this;
    }

    public function getDurationInMinutes(): int
    {
        if ($this->duration instanceof \DateTimeInterface) {
            return ((int)$this->duration->format('H') * 60) + (int)$this->duration->format('i');
        }

        throw new \InvalidArgumentException('La durée doit être de type DateTimeInterface.');
    }

    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(Variant $variant): self
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            // Évite de réassigner inutilement si la prestation est déjà correctement définie
            if ($variant->getPrestation() !== $this) {
                $variant->setPrestation($this);
            }
        }

        return $this;
    }

    public function removeVariant(Variant $variant): self
    {
        if ($this->variants->removeElement($variant)) {
            // Évite une mise à null inutile si la relation est déjà rompue
            if ($variant->getPrestation() === $this) {
                $variant->setPrestation(null);
            }
        }

        return $this;
    }

}
