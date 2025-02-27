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
<<<<<<< Updated upstream
=======

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $createur = null;
    public function getCreateur(): ?Utilisateur
    {
        return $this->createur;
    }

    public function setCreateur(?Utilisateur $createur): self
    {
        $this->createur = $createur;
        return $this;
    }
        

    #[ORM\ManyToOne(targetEntity: Partenariat::class, inversedBy: 'candidatures')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Partenariat $partenariat = null;

>>>>>>> Stashed changes

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
<<<<<<< Updated upstream
}
=======

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(string $cv): static
    {
        $this->cv = $cv;

        return $this;
    }

    public function getPortfolio(): ?string
    {
        return $this->portfolio;
    }

    public function setPortfolio(string $portfolio): static
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    public function getMotivation(): ?string
    {
        return $this->motivation;
    }

    public function setMotivation(string $motivation): static
    {
        $this->motivation = $motivation;

        return $this;
    }

    

    public function getTypeCollab(): ?string
    {
        return $this->typeCollab;
    }

    public function setTypeCollab(string $typeCollab): static
    {
        $this->typeCollab = $typeCollab;

        return $this;
    }
    public function __construct()
    {
        $this->datePostulation = new \DateTime(); // Date système par défaut
    }


} 
>>>>>>> Stashed changes
