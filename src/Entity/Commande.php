<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
////
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datecmd = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;
    

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class, inversedBy: 'commandes')]
    #[ORM\JoinTable(name: "commandeProduits")]
    #[ORM\JoinColumn(name: "commande_id", referencedColumnName: "id", onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(name: "produit_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private Collection $produit;

    public function __construct()
    {
        $this->produit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDatecmd(): ?\DateTimeInterface
    {
        return $this->datecmd;
    }

    public function setDatecmd(\DateTimeInterface $datecmd): static
    {
        $this->datecmd = $datecmd;
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

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        return $this;
    }
    ///
    private ?EntityManagerInterface $entityManager = null;

public function setEntityManager(EntityManagerInterface $entityManager): void
{
    $this->entityManager = $entityManager;
}

public function setQuantiteProduit(Produit $produit, int $quantite, EntityManagerInterface $entityManager): void
{
    $conn = $entityManager->getConnection();
    
    
    $sqlCheck = "SELECT COUNT(*) FROM commande_produit WHERE commande_id = :commande_id AND produit_id = :produit_id";
    $stmtCheck = $conn->prepare($sqlCheck);
    $count = $stmtCheck->executeQuery([
        'commande_id' => $this->getId(),
        'produit_id' => $produit->getId(),
    ])->fetchOne();

    if ($count > 0) {
        
        $sql = "UPDATE commande_produit SET quantite = :quantite WHERE commande_id = :commande_id AND produit_id = :produit_id";
    } else {
      
        $sql = "INSERT INTO commande_produit (commande_id, produit_id, quantite) VALUES (:commande_id, :produit_id, :quantite)";
    }

    $stmt = $conn->prepare($sql);
    $stmt->executeQuery([
        'commande_id' => $this->getId(),
        'produit_id' => $produit->getId(),
        'quantite' => $quantite,
    ]);

    $entityManager->flush(); 
}
public function getQuantiteProduit(Produit $produit): ?int
{
    if (!$this->entityManager) {
        throw new \LogicException('EntityManager must be set before calling getQuantiteProduit.');
    }

    $conn = $this->entityManager->getConnection();
    $sql = "SELECT quantite FROM commande_produit WHERE commande_id = :commande_id AND produit_id = :produit_id";
    $stmt = $conn->prepare($sql);
    $result = $stmt->executeQuery([
        'commande_id' => $this->getId(),
        'produit_id' => $produit->getId(),
    ]);

    return $result->fetchOne() ?: null;
}
public function recalculerPrixTotal(): void
{
    if (!$this->entityManager) {
        throw new \LogicException('EntityManager must be set before calling recalculerPrixTotal.');
    }

    $totalPrix = 0;
    foreach ($this->produit as $produit) {
        $quantite = $this->getQuantiteProduit($produit);
        $totalPrix += $produit->getPrix() * $quantite;
    }

    $this->setPrix($totalPrix);

    
    $this->entityManager->flush();
}
//new
public function verifierStockDisponible(Produit $produit, int $quantite): bool
{
    return $quantite <= $produit->getQuantitestock();
}
}
