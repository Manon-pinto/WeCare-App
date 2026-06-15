<?php

namespace App\Entity;

use App\Repository\DemandeDevisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeDevisRepository::class)]
#[ORM\Table(name: 'demande_devis')]
class DemandeDevis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $prenom;

    #[ORM\Column(length: 100)]
    private string $nom;

    #[ORM\Column(length: 150)]
    private string $email;

    #[ORM\Column(length: 20)]
    private string $telephone;

    #[ORM\Column(length: 150)]
    private string $structure;

    #[ORM\Column(length: 50)]
    private string $typeStructure;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $nbIntervenants = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $nbBeneficiaires = null;

    #[ORM\Column(length: 20)]
    private string $plan = 'gratuit';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(length: 20)]
    private string $statut = 'en_attente';

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getTelephone(): string { return $this->telephone; }
    public function setTelephone(string $telephone): static { $this->telephone = $telephone; return $this; }

    public function getStructure(): string { return $this->structure; }
    public function setStructure(string $structure): static { $this->structure = $structure; return $this; }

    public function getTypeStructure(): string { return $this->typeStructure; }
    public function setTypeStructure(string $typeStructure): static { $this->typeStructure = $typeStructure; return $this; }

    public function getNbIntervenants(): ?string { return $this->nbIntervenants; }
    public function setNbIntervenants(?string $nbIntervenants): static { $this->nbIntervenants = $nbIntervenants; return $this; }

    public function getNbBeneficiaires(): ?string { return $this->nbBeneficiaires; }
    public function setNbBeneficiaires(?string $nbBeneficiaires): static { $this->nbBeneficiaires = $nbBeneficiaires; return $this; }

    public function getPlan(): string { return $this->plan; }
    public function setPlan(string $plan): static { $this->plan = $plan; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): static { $this->message = $message; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getMontant(): ?float { return $this->montant; }
    public function setMontant(?float $montant): static { $this->montant = $montant; return $this; }

    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): static { $this->note = $note; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
}
