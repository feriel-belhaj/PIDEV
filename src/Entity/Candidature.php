<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datePostulation = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'candidature')]
    private ?Partenariat $partenariat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePostulation(): ?\DateTimeInterface
    {
        return $this->datePostulation;
    }

    public function setDatePostulation(\DateTimeInterface $datePostulation): static
    {
        $this->datePostulation = $datePostulation;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPartenariat(): ?Partenariat
    {
        return $this->partenariat;
    }

    public function setPartenariat(?Partenariat $partenariat): static
    {
        $this->partenariat = $partenariat;

        return $this;
    }
}
