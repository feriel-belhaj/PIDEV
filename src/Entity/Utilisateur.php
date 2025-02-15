<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $mdp = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    /**
     * @var Collection<int, Formation>
     */
    #[ORM\ManyToMany(targetEntity: Formation::class, inversedBy: 'utilisateurs')]
    private Collection $formations;

    /**
     * @var Collection<int, Creation>
     */
    #[ORM\OneToMany(targetEntity: Creation::class, mappedBy: 'utilisateur')]
    private Collection $creation;

    /**
     * @var Collection<int, Evenement>
     */
    #[ORM\ManyToMany(targetEntity: Evenement::class, inversedBy: 'utilisateurs')]
    private Collection $evennement;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class, inversedBy: 'produits')]
    private Collection $produit;

    /**
     * @var Collection<int, Partenariat>
     */
    #[ORM\ManyToMany(targetEntity: Partenariat::class, inversedBy: 'utilisateurs')]
    private Collection $partenariats;

    public function __construct()
    {
        $this->formation = new ArrayCollection();
        $this->creation = new ArrayCollection();
        $this->evennement = new ArrayCollection();
        $this->produit = new ArrayCollection();
        $this->partenariats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): static
    {
        $this->mdp = $mdp;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

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

    public function getRole(): string 
    {
        return $this->role;
    }

    public function setRole(string $role): static 
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getFormation(): Collection
    {
        return $this->formation;
    }

    public function addFormation(Formation $formation): static
    {
        if (!$this->formation->contains($formation)) {
            $this->formation->add($formation);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): static
    {
        $this->formation->removeElement($formation);

        return $this;
    }

    /**
     * @return Collection<int, Creation>
     */
    public function getCreation(): Collection
    {
        return $this->creation;
    }

    public function addCreation(Creation $creation): static
    {
        if (!$this->creation->contains($creation)) {
            $this->creation->add($creation);
            $creation->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCreation(Creation $creation): static
    {
        if ($this->creation->removeElement($creation)) {
            // set the owning side to null (unless already changed)
            if ($creation->getUtilisateur() === $this) {
                $creation->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvennement(): Collection
    {
        return $this->evennement;
    }

    public function addEvennement(Evenement $evennement): static
    {
        if (!$this->evennement->contains($evennement)) {
            $this->evennement->add($evennement);
        }

        return $this;
    }

    public function removeEvennement(Evenement $evennement): static
    {
        $this->evennement->removeElement($evennement);

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduit(): Collection
    {
        return $this->produit;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produit->contains($produit)) {
            $this->produit->add($produit);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        $this->produit->removeElement($produit);

        return $this;
    }

    /**
     * @return Collection<int, Partenariat>
     */
    public function getPartenariats(): Collection
    {
        return $this->partenariats;
    }

    public function addPartenariat(Partenariat $partenariat): static
    {
        if (!$this->partenariats->contains($partenariat)) {
            $this->partenariats->add($partenariat);
        }

        return $this;
    }

    public function removePartenariat(Partenariat $partenariat): static
    {
        $this->partenariats->removeElement($partenariat);

        return $this;
    }
}
