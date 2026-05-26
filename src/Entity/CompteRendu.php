<?php

namespace App\Entity;

use App\Enum\ModeRedaction;
use App\Repository\CompteRenduRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompteRenduRepository::class)]
#[ORM\Table(name: 'compte_rendu')]
class CompteRendu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Intervention::class, inversedBy: 'compteRendu')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Intervention $intervention = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateRedaction = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(enumType: ModeRedaction::class)]
    private ?ModeRedaction $modeRedaction = null;

    #[ORM\Column]
    private ?bool $valide = null;

    #[ORM\OneToMany(mappedBy: 'compteRendu', targetEntity: Notification::class)]
    private Collection $notifications;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
    }

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

    public function getDateRedaction(): ?\DateTimeInterface
    {
        return $this->dateRedaction;
    }

    public function setDateRedaction(\DateTimeInterface $dateRedaction): static
    {
        $this->dateRedaction = $dateRedaction;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getModeRedaction(): ?ModeRedaction
    {
        return $this->modeRedaction;
    }

    public function setModeRedaction(ModeRedaction $modeRedaction): static
    {
        $this->modeRedaction = $modeRedaction;
        return $this;
    }

    public function isValide(): ?bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): static
    {
        $this->valide = $valide;
        return $this;
    }

    /** @return Collection<int, Notification> */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setCompteRendu($this);
        }
        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        $this->notifications->removeElement($notification);
        return $this;
    }
}
