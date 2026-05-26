<?php

namespace App\Entity;

use App\Enum\GraviteIncident;
use App\Enum\StatutIncident;
use App\Repository\IncidentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IncidentRepository::class)]
#[ORM\Table(name: 'incident')]
class Incident
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Intervention::class, inversedBy: 'incidents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Intervention $intervention = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSignalement = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(enumType: GraviteIncident::class)]
    private ?GraviteIncident $gravite = null;

    #[ORM\Column(enumType: StatutIncident::class)]
    private ?StatutIncident $statut = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $zone = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?Intervention $intervention): static
    {
        $this->intervention = $intervention;
        return $this;
    }

    public function getDateSignalement(): ?\DateTimeInterface
    {
        return $this->dateSignalement;
    }

    public function setDateSignalement(\DateTimeInterface $dateSignalement): static
    {
        $this->dateSignalement = $dateSignalement;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getGravite(): ?GraviteIncident
    {
        return $this->gravite;
    }

    public function setGravite(GraviteIncident $gravite): static
    {
        $this->gravite = $gravite;
        return $this;
    }

    public function getStatut(): ?StatutIncident
    {
        return $this->statut;
    }

    public function setStatut(StatutIncident $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(?string $zone): static
    {
        $this->zone = $zone;
        return $this;
    }
}
