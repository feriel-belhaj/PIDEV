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

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
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

    #[ORM\ManyToOne(targetEntity: Partenariat::class, inversedBy: 'candidature')]
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
    private ?string $motivation = null;

    #[ORM\Column(length: 255)]
    private ?string $typeCollab = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $scoreNLP = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $scoreArtistique = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $analysisResult = null;


    public function getAnalysisResult(): ?string
    {
        return $this->analysisResult;
    }

    public function getAnalysisResultDecoded(): array
    {
        $decoded = json_decode($this->analysisResult, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function setAnalysisResult(?array $analysisResult): static
    {
        // Vérifie que le tableau est valide avant de l'encoder
        if ($analysisResult !== null) {
            $jsonResult = json_encode($analysisResult);
    
            // Vérifie si l'encodage JSON a réussi
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erreur lors de l\'encodage JSON : ' . json_last_error_msg());
            }
    
            $this->analysisResult = $jsonResult;
        } else {
            $this->analysisResult = null;
        }
    
        return $this;
    }

    // Getters et Setters pour les autres propriétés...

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

    public function getScoreNLP(): ?float
    {
        return $this->scoreNLP;
    }

    public function setScoreNLP(?float $scoreNLP): static
    {
        $this->scoreNLP = $scoreNLP;
        return $this;
    }

    public function getScoreArtistique(): ?float
    {
        return $this->scoreArtistique;
    }

    public function setScoreArtistique(?float $scoreArtistique): static
    {
        $this->scoreArtistique = $scoreArtistique;
        return $this;
    }
}