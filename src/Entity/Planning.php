<?php

namespace App\Entity;

use App\Enum\StatutPlanning;
use App\Repository\PlanningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningRepository::class)]
#[ORM\Table(name: 'planning')]
class Planning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Administrateur::class, inversedBy: 'plannings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Administrateur $admin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $semaine = null;

    #[ORM\Column(enumType: StatutPlanning::class)]
    private ?StatutPlanning $statut = null;

    #[ORM\ManyToMany(targetEntity: Intervenant::class, inversedBy: 'plannings')]
    #[ORM\JoinTable(name: 'planning_intervenant')]
    private Collection $intervenants;

    #[ORM\OneToMany(mappedBy: 'planning', targetEntity: Intervention::class)]
    private Collection $interventions;

    public function __construct()
    {
        $this->intervenants = new ArrayCollection();
        $this->interventions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdmin(): ?Administrateur
    {
        return $this->admin;
    }

    public function setAdmin(?Administrateur $admin): static
    {
        $this->admin = $admin;
        return $this;
    }

    public function getSemaine(): ?\DateTimeInterface
    {
        return $this->semaine;
    }

    public function setSemaine(\DateTimeInterface $semaine): static
    {
        $this->semaine = $semaine;
        return $this;
    }

    public function getStatut(): ?StatutPlanning
    {
        return $this->statut;
    }

    public function setStatut(StatutPlanning $statut): static
    {
        $this->statut = $statut;
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
        }
        return $this;
    }

    public function removeIntervenant(Intervenant $intervenant): static
    {
        $this->intervenants->removeElement($intervenant);
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
            $intervention->setPlanning($this);
        }
        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        $this->interventions->removeElement($intervention);
        return $this;
    }
}
