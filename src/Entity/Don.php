<?php

namespace App\Entity;

use App\Repository\DonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DonRepository::class)]
class Don
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\Column]
    private ?\DateTimeImmutable $donationdate = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $paymentref = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

<<<<<<< Updated upstream
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?evenement $evenement = null;
=======
    #[ORM\ManyToOne(inversedBy: 'dons')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Evenement $evenement = null;
    
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
>>>>>>> Stashed changes

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getDonationdate(): ?\DateTimeImmutable
    {
        return $this->donationdate;
    }

    public function setDonationdate(\DateTimeImmutable $donationdate): static
    {
        $this->donationdate = $donationdate;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPaymentref(): ?string
    {
        return $this->paymentref;
    }

    public function setPaymentref(string $paymentref): static
    {
        $this->paymentref = $paymentref;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getEvenement(): ?evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?evenement $evenement): static
    {
        $this->evenement = $evenement;

        return $this;
    }
}
