<?php

namespace App\Entity;

use App\Enum\TypeNotification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: CompteRendu::class, inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: true)]
    private ?CompteRendu $compteRendu = null;

    #[ORM\Column(enumType: TypeNotification::class)]
    private ?TypeNotification $type = null;

    #[ORM\Column]
    private ?bool $lu = null;

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

    public function getCompteRendu(): ?CompteRendu
    {
        return $this->compteRendu;
    }

    public function setCompteRendu(?CompteRendu $compteRendu): static
    {
        $this->compteRendu = $compteRendu;
        return $this;
    }

    public function getType(): ?TypeNotification
    {
        return $this->type;
    }

    public function setType(TypeNotification $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function isLu(): ?bool
    {
        return $this->lu;
    }

    public function setLu(bool $lu): static
    {
        $this->lu = $lu;
        return $this;
    }
}
