<?php

namespace App\Entity;

use App\Repository\CertificatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificatRepository::class)]
class Certificat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateobt = null;

    #[ORM\Column(length: 255)]
    private ?string $niveau = null;

    #[ORM\Column(length: 255)]
    private ?string $nomorganisme = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
<<<<<<< Updated upstream
    private ?formation $formation = null;
=======
    private ?Formation $formation = null;

    

    public function __construct()
    {
        $this->dateobt = new \DateTime();
        $this->nom = '';
        $this->prenom = '';
        $this->niveau = '';
        $this->nomorganisme = '';
    }
>>>>>>> Stashed changes

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

    public function getDateobt(): ?\DateTimeInterface
    {
        return $this->dateobt;
    }

    public function setDateobt(\DateTimeInterface $dateobt): static
    {
        $this->dateobt = $dateobt;

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

    public function getNomorganisme(): ?string
    {
        return $this->nomorganisme;
    }

    public function setNomorganisme(string $nomorganisme): static
    {
        $this->nomorganisme = $nomorganisme;

        return $this;
    }

    public function getFormation(): ?formation
    {
        return $this->formation;
    }

    public function setFormation(?formation $formation): static
    {
        $this->formation = $formation;

        return $this;
    }
}
