<?php

namespace App\Entity;

use App\Repository\DateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DateRepository::class)]
class Date
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: 'La date ne doit pas être vide.')]
    #[Assert\Date(message: 'La date doit être valide.')]
    private ?\DateTimeInterface $date = null;

    #[ORM\OneToMany(targetEntity: Hours::class, mappedBy: 'date', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $hours;

    public function __construct()
    {
        $this->hours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
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
            $this->hours[] = $hour;
            $hour->setDate($this);
        }

        return $this;
    }

    public function removeHour(Hours $hour): self
    {
        if ($this->hours->removeElement($hour) && $hour->getDate() === $this) {
            $hour->setDate(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        if (!$this->date) {
            return '';
        }
        // Formate la date en utilisant le format 'j F Y' (ex: "13 February 2025")
        $formatted = $this->date->format('j F Y');
        // Tableau de correspondance des noms de mois anglais vers français
        $months = [
            'January'   => 'janvier',
            'February'  => 'février',
            'March'     => 'mars',
            'April'     => 'avril',
            'May'       => 'mai',
            'June'      => 'juin',
            'July'      => 'juillet',
            'August'    => 'août',
            'September' => 'septembre',
            'October'   => 'octobre',
            'November'  => 'novembre',
            'December'  => 'décembre',
        ];
        // Remplace le nom du mois anglais par son équivalent français
        return strtr($formatted, $months);
    }
}
