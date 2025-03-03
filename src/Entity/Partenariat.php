<?php

namespace App\Entity;

use App\Repository\PartenariatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PartenariatRepository::class)]
class Partenariat
{

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'partenariats')]
    private Collection $utilisateurs;

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
    
    #[ORM\OneToMany(mappedBy: 'partenariat', targetEntity: Candidature::class, cascade: ['remove'])]
    private Collection $candidatures;

    public function __construct()
    {
        $this->dateDebut = new \DateTime(); // ou toute autre date par défaut
    $this->dateFin = new \DateTime();   // ou toute autre date par défaut
        $this->candidatures = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
    }


    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
#[Assert\NotBlank(message: "Le nom est obligatoire.")]
#[Assert\Length(max: 50, maxMessage: "Le nom ne peut pas dépasser 50 caractères.")]
#[Assert\Regex(
    pattern: "/^[A-ZÀ-ÿ][a-zA-ZÀ-ÿéèêëàâäçôùùîï]+( [a-zA-ZÀ-ÿéèêëàâäçôùùîï]+)*$/",
    message: "Le nom doit commencer par une majuscule et peut contenir des lettres accentuées et des espaces."
)]
private ?string $Nom = null;


#[ORM\Column(length: 50)]
#[Assert\NotBlank(message: "Le type est obligatoire.")]
#[Assert\Length(max: 50, maxMessage: "Le type ne peut pas dépasser 50 caractères.")]
#[Assert\Regex(
    pattern: "/^[A-ZÀ-ÿ][a-zA-ZÀ-ÿéèêëàâäçôùùîï]+( [a-zA-ZÀ-ÿéèêëàâäçôùùîï]+)*$/",
    message: "Le type doit commencer par une majuscule et peut contenir des lettres accentuées et des espaces."
)]
private ?string $Type = null;


    #[ORM\Column(length: 255)]
    private ?string $description = null;

    
    #[Assert\NotNull(message: "Le statut ne peut pas être null.")]
#[ORM\Column(length: 255)]
    private ?string $statut = 'actif';  // Définir une valeur par défaut, par exemple "actif"
    

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): static
    {
        $this->Type = $Type;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }
    
    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->addPartenariat($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            $utilisateur->removePartenariat($this);
        }

        return $this;
    }
}