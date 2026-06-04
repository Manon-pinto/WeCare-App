<?php

namespace App\Entity;

use App\Enum\StatutIntervention;
use App\Enum\TypeIntervention;
use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
#[ORM\Table(name: 'intervention')]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Intervenant::class, inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Intervenant $intervenant = null;

    #[ORM\ManyToOne(targetEntity: Beneficiaire::class, inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Beneficiaire $beneficiaire = null;

    #[ORM\ManyToOne(targetEntity: Planning::class, inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Planning $planning = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(enumType: StatutIntervention::class)]
    private ?StatutIntervention $statut = null;

    #[ORM\Column(enumType: TypeIntervention::class)]
    private ?TypeIntervention $typeIntervention = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\OneToOne(mappedBy: 'intervention', targetEntity: CompteRendu::class, cascade: ['persist', 'remove'])]
    private ?CompteRendu $compteRendu = null;

    #[ORM\OneToMany(mappedBy: 'intervention', targetEntity: Incident::class, cascade: ['remove'])]
    private Collection $incidents;

    public function __construct()
    {
        $this->incidents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntervenant(): ?Intervenant
    {
        return $this->intervenant;
    }

    public function setIntervenant(?Intervenant $intervenant): static
    {
        $this->intervenant = $intervenant;
        return $this;
    }

    public function getBeneficiaire(): ?Beneficiaire
    {
        return $this->beneficiaire;
    }

    public function setBeneficiaire(?Beneficiaire $beneficiaire): static
    {
        $this->beneficiaire = $beneficiaire;
        return $this;
    }

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(?Planning $planning): static
    {
        $this->planning = $planning;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getStatut(): ?StatutIntervention
    {
        return $this->statut;
    }

    public function setStatut(StatutIntervention $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getTypeIntervention(): ?TypeIntervention
    {
        return $this->typeIntervention;
    }

    public function setTypeIntervention(TypeIntervention $typeIntervention): static
    {
        $this->typeIntervention = $typeIntervention;
        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;
        return $this;
    }

    public function getCompteRendu(): ?CompteRendu
    {
        return $this->compteRendu;
    }

    public function setCompteRendu(?CompteRendu $compteRendu): static
    {
        $this->compteRendu = $compteRendu;
        return $this;
    }

    /** @return Collection<int, Incident> */
    public function getIncidents(): Collection
    {
        return $this->incidents;
    }

    public function addIncident(Incident $incident): static
    {
        if (!$this->incidents->contains($incident)) {
            $this->incidents->add($incident);
            $incident->setIntervention($this);
        }
        return $this;
    }

    public function removeIncident(Incident $incident): static
    {
        $this->incidents->removeElement($incident);
        return $this;
    }
}
