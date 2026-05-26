<?php

namespace App\Entity;

use App\Enum\NiveauAcces;
use App\Repository\AdministrateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdministrateurRepository::class)]
#[ORM\Table(name: 'administrateur')]
class Administrateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Utilisateur::class, inversedBy: 'administrateur')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(enumType: NiveauAcces::class)]
    private ?NiveauAcces $niveauAcces = null;

    #[ORM\OneToMany(mappedBy: 'adminCreateur', targetEntity: Intervenant::class)]
    private Collection $intervenants;

    #[ORM\OneToMany(mappedBy: 'adminCreateur', targetEntity: Beneficiaire::class)]
    private Collection $beneficiaires;

    #[ORM\OneToMany(mappedBy: 'admin', targetEntity: Planning::class)]
    private Collection $plannings;

    public function __construct()
    {
        $this->intervenants = new ArrayCollection();
        $this->beneficiaires = new ArrayCollection();
        $this->plannings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getNiveauAcces(): ?NiveauAcces
    {
        return $this->niveauAcces;
    }

    public function setNiveauAcces(NiveauAcces $niveauAcces): static
    {
        $this->niveauAcces = $niveauAcces;
        return $this;
    }

    /** @return Collection<int, Intervenant> */
    public function getIntervenants(): Collection
    {
        return $this->intervenants;
    }

    public function addIntervenant(Intervenant $intervenant): static
    {
        if (!$this->intervenants->contains($intervenant)) {
            $this->intervenants->add($intervenant);
            $intervenant->setAdminCreateur($this);
        }
        return $this;
    }

    public function removeIntervenant(Intervenant $intervenant): static
    {
        $this->intervenants->removeElement($intervenant);
        return $this;
    }

    /** @return Collection<int, Beneficiaire> */
    public function getBeneficiaires(): Collection
    {
        return $this->beneficiaires;
    }

    public function addBeneficiaire(Beneficiaire $beneficiaire): static
    {
        if (!$this->beneficiaires->contains($beneficiaire)) {
            $this->beneficiaires->add($beneficiaire);
            $beneficiaire->setAdminCreateur($this);
        }
        return $this;
    }

    public function removeBeneficiaire(Beneficiaire $beneficiaire): static
    {
        $this->beneficiaires->removeElement($beneficiaire);
        return $this;
    }

    /** @return Collection<int, Planning> */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): static
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings->add($planning);
            $planning->setAdmin($this);
        }
        return $this;
    }

    public function removePlanning(Planning $planning): static
    {
        $this->plannings->removeElement($planning);
        return $this;
    }
}
