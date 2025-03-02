<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
private ?\DateTimeInterface $datePostulation = null;


    

#[ORM\ManyToOne(targetEntity: Partenariat::class, inversedBy: 'candidatures')]
#[ORM\JoinColumn(onDelete: "CASCADE")]
private ?Partenariat $partenariat = null;


    #[ORM\Column(length: 255)]
    private ?string $cv = null;

    #[ORM\Column(length: 255)]
    private ?string $portfolio = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        min: 10,
        minMessage: "Le texte de motivation doit contenir au moins 10 caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[A-Z][A-Za-zÀ-ÖØ-öø-ÿ0-9 .,!?'-]*$/",
        message: "La motivation doit commencer par une majuscule et ne contenir que des lettres, chiffres, espaces et ponctuations."
    )]
    private ?string $motivation = null;

    #[ORM\Column(length: 255)]
    private ?string $typeCollab = null;

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

   
    public function getPartenariat(): ?Partenariat
    {
        return $this->partenariat;
    }

    public function setPartenariat(?Partenariat $partenariat): static
    {
        $this->partenariat = $partenariat;

        return $this;
    }

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