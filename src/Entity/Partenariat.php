<?php

namespace App\Entity;

use App\Repository\PartenariatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartenariatRepository::class)]
class Partenariat
{
<<<<<<< Updated upstream
=======

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'partenariats')]
    private Collection $utilisateurs;

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

    #[ORM\OneToMany(mappedBy: 'partenariat', targetEntity: Candidature::class, cascade: ['remove'])]
    private Collection $candidatures;
    

    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
    }

    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }
    
>>>>>>> Stashed changes
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

<<<<<<< Updated upstream
    #[ORM\Column(type: Types::DATE_MUTABLE)]
=======
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(max: 50, maxMessage: "Le nom ne peut pas dépasser 50 caractères.")]
    #[Assert\Regex(
        pattern: "/^[A-Z][a-zA-Z ]+$/",
        message: "Le nom doit commencer par une majuscule et ne contenir que des lettres ."
    )]
    private ?string $nom = null;


    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le type est obligatoire.")]
    #[Assert\Length(max: 50, maxMessage: "Le type ne peut pas dépasser 50 caractères.")]
    #[Assert\Regex(
        pattern: "/^[A-Z][a-zA-Z ]+$/",
        message: "Le type doit commencer par une majuscule et ne contenir que des lettres ."
    )]
    private ?string $type = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins 10 caractères.",
        max: 255,
        maxMessage: "La description ne peut pas dépasser 255 caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[A-Z][a-zA-Z ,;.:\'\"!?-]+$/",
        message: "La description doit commencer par une majuscule et contenir uniquement des lettres, des espaces et des signes de ponctuation."
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)] // nullable: true pour permettre null en DB
    #[Assert\Choice(
        choices: ['actif', 'en cours', 'expiré'],
        message: "Le statut doit être 'actif', 'en cours' ou 'expiré'."
    )]
    private ?string $statut = null;


    #[ORM\Column(length: 255)]
    private ?string $image = null;

   #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de début est obligatoire.")]
    #[Assert\Type("\DateTimeInterface")]
>>>>>>> Stashed changes
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\GreaterThan(propertyPath: "dateDebut", message: "La date de fin doit être supérieure à la date de début.")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'partenariat')]
    private Collection $candidature;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'partenariats')]
    private Collection $utilisateurs;

    public function __construct()
    {
        $this->candidature = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

<<<<<<< Updated upstream
=======
    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
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

>>>>>>> Stashed changes
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

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

    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidature(): Collection
    {
        return $this->candidature;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidature->contains($candidature)) {
            $this->candidature->add($candidature);
            $candidature->setPartenariat($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidature->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getPartenariat() === $this) {
                $candidature->setPartenariat(null);
            }
        }

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
