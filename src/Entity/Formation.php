<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datedeb = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datefin = null;

    #[ORM\Column(length: 255)]
    private ?string $niveau = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(length: 255)]
    private ?string $emplacement = null;

    #[ORM\Column]
    private ?int $nbplace = null;

    #[ORM\Column]
    private ?int $nbparticipant = null;

    #[ORM\Column(length: 255)]
    private ?string $organisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $duree = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'formation')]
    private Collection $utilisateurs;

<<<<<<< Updated upstream
    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
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

     /**
     * @var Collection<int, Certificat>
     */
    #[ORM\OneToMany(targetEntity: Certificat::class, mappedBy: 'formation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $certificats;

    #[ORM\OneToMany(mappedBy: 'formation', targetEntity: FormationReservee::class, cascade: ['remove'])]
    private Collection $formationsReservees;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
        $this->certificats = new ArrayCollection();
        $this->formationsReservees = new ArrayCollection();
        $this->datedeb = new \DateTime();
        $this->datefin = new \DateTime();
>>>>>>> Stashed changes
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function getDatedeb(): ?\DateTimeInterface
    {
        return $this->datedeb;
    }

    public function setDatedeb(\DateTimeInterface $datedeb): static
    {
        $this->datedeb = $datedeb;

        return $this;
    }

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): static
    {
        $this->datefin = $datefin;

        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(string $emplacement): static
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    public function getNbplace(): ?int
    {
        return $this->nbplace;
    }

    public function setNbplace(int $nbplace): static
    {
        $this->nbplace = $nbplace;

        return $this;
    }

    public function getNbparticipant(): ?int
    {
        return $this->nbparticipant;
    }

    public function setNbparticipant(int $nbparticipant): static
    {
        $this->nbparticipant = $nbparticipant;

        return $this;
    }

    public function getOrganisateur(): ?string
    {
        return $this->organisateur;
    }

    public function setOrganisateur(string $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(string $duree): static
    {
        $this->duree = $duree;

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
            $utilisateur->addFormation($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            $utilisateur->removeFormation($this);
        }

        return $this;
    }
<<<<<<< Updated upstream
}
=======

    public function __toString(): string
    {
        return $this->titre ?? '';
    }

    /**
     * @return Collection<int, FormationReservee>
     */
    public function getFormationsReservees(): Collection
    {
        return $this->formationsReservees;
    }

    public function addFormationReservee(FormationReservee $formationReservee): self
    {
        if (!$this->formationsReservees->contains($formationReservee)) {
            $this->formationsReservees->add($formationReservee);
            $formationReservee->setFormation($this);
        }

        return $this;
    }

    public function removeFormationReservee(FormationReservee $formationReservee): self
    {
        if ($this->formationsReservees->removeElement($formationReservee)) {
            // set the owning side to null (unless already changed)
            if ($formationReservee->getFormation() === $this) {
                $formationReservee->setFormation(null);
            }
        }

        return $this;
    }
}
>>>>>>> Stashed changes
