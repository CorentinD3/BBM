<?php

namespace App\Entity;

use App\Repository\HoursTemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HoursTemplateRepository::class)]
class HoursTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'time')]
    #[Assert\NotBlank(message: 'L\'heure de template ne doit pas être vide.')]
    #[Assert\Time(message: 'L\'heure de template doit être valide.')]
    private ?\DateTimeInterface $hour = null;

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
}
