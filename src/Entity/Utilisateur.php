<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z]+$/",
        message: "Le nom ne doit contenir que des lettres."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s]+$/",
        message: "Le prénom ne doit contenir que des lettres et des espaces."
    )]
    private ?string $prenom = null;

    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\d\s]).+$/",
        message: "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial."
    )]
    private ?string $password = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse ne peut pas être vide.")]
    private ?string $adresse = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le numéro de téléphone ne peut pas être vide.")]
    #[Assert\Length(min: 8, max: 8, exactMessage: "Le numéro de téléphone doit comporter exactement {{ limit }} caractères.")]
    private ?string $telephone = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInscription = null;
    
    #[ORM\Column(length: 255)]
    private ?string $image = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le rôle ne peut pas être vide.")]
    private ?string $role = null;

   
    
    #[ORM\Column(length: 255)]
    private ?string $Sexe = null;

    /**
     * @var Collection<int, Formation>
     */
    #[ORM\ManyToMany(targetEntity: Formation::class, inversedBy: 'utilisateurs')]
    private Collection $formation;

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
        $this->dateInscription = new \DateTime();
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
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

    public function setDateInscription(?\DateTime $date = null): self
    {
        $this->dateInscription = $date ?? new \DateTime();
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

    public function getRoles(): array
    {
        return [$this->role];
    }
    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $validRoles = ['ROLE_VISITEUR', 'ROLE_ADMIN', 'ROLE_CLIENT', 'ROLE_ARTISAN'];

        if ($role === null || !in_array($role, $validRoles)) {
            $role = 'ROLE_VISITEUR';
        }

        $this->role = $role;
        return $this;
    }
    public function getUserIdentifier(): string
{
    return $this->email; //login
}

    public function eraseCredentials(): void
    {

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

    public function getSexe(): ?string
    {
        return $this->Sexe;
    }

    public function setSexe(string $Sexe): static
    {
        $this->Sexe = $Sexe;

        return $this;
    }
}
