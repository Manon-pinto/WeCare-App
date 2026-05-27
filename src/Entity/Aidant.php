<?php

namespace App\Entity;

use App\Repository\AidantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AidantRepository::class)]
#[ORM\Table(name: 'aidant')]
class Aidant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Beneficiaire::class, inversedBy: 'aidants')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Beneficiaire $beneficiaire = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lien = null;

    public function getId(): ?int { return $this->id; }

    public function getBeneficiaire(): ?Beneficiaire { return $this->beneficiaire; }
    public function setBeneficiaire(?Beneficiaire $beneficiaire): static { $this->beneficiaire = $beneficiaire; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getNomComplet(): string
    {
        return trim(($this->prenom ? $this->prenom . ' ' : '') . ($this->nom ?? ''));
    }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): static { $this->telephone = $telephone; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }

    public function getLien(): ?string { return $this->lien; }
    public function setLien(?string $lien): static { $this->lien = $lien; return $this; }
}
