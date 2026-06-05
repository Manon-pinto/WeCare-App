<?php

namespace App\Entity;

use App\Repository\IntervenantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IntervenantRepository::class)]
#[ORM\Table(name: 'intervenant')]
class Intervenant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Utilisateur::class, inversedBy: 'intervenant')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Administrateur::class, inversedBy: 'intervenants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Administrateur $adminCreateur = null;

    #[ORM\Column(length: 255)]
    private ?string $specialite = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?bool $disponibilite = null;

    #[ORM\Column(length: 30, nullable: true, options: ['default' => 'actif'])]
    private ?string $statut = 'actif';

    #[ORM\Column]
    private ?float $rayonIntervention = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $vehicule = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToMany(targetEntity: Planning::class, mappedBy: 'intervenants')]
    private Collection $plannings;

    #[ORM\OneToMany(mappedBy: 'intervenant', targetEntity: Intervention::class)]
    private Collection $interventions;

    public function __construct()
    {
        $this->plannings = new ArrayCollection();
        $this->interventions = new ArrayCollection();
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

    public function getAdminCreateur(): ?Administrateur
    {
        return $this->adminCreateur;
    }

    public function setAdminCreateur(?Administrateur $adminCreateur): static
    {
        $this->adminCreateur = $adminCreateur;
        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;
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

    public function isDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): static
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut ?? 'actif';
    }

    public function setStatut(string $statut): static
    {
        $this->statut      = $statut;
        $this->disponibilite = ($statut === 'actif');
        return $this;
    }

    public function getRayonIntervention(): ?float
    {
        return $this->rayonIntervention;
    }

    public function setRayonIntervention(float $rayonIntervention): static
    {
        $this->rayonIntervention = $rayonIntervention;
        return $this;
    }

    public function getVehicule(): ?string { return $this->vehicule; }
    public function setVehicule(?string $vehicule): static { $this->vehicule = $vehicule; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    /** @return Collection<int, Planning> */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): static
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings->add($planning);
            $planning->addIntervenant($this);
        }
        return $this;
    }

    public function removePlanning(Planning $planning): static
    {
        if ($this->plannings->removeElement($planning)) {
            $planning->removeIntervenant($this);
        }
        return $this;
    }

    /** @return Collection<int, Intervention> */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setIntervenant($this);
        }
        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        $this->interventions->removeElement($intervention);
        return $this;
    }
}
