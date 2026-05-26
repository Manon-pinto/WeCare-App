<?php

namespace App\Entity;

use App\Enum\TypeMessage;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'message')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'messagesEnvoyes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $expediteur = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'messagesRecus')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $destinataire = null;

    #[ORM\ManyToOne(targetEntity: Beneficiaire::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Beneficiaire $beneficiaire = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnvoi = null;

    #[ORM\Column]
    private ?bool $lu = null;

    #[ORM\Column(enumType: TypeMessage::class)]
    private ?TypeMessage $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpediteur(): ?Utilisateur
    {
        return $this->expediteur;
    }

    public function setExpediteur(?Utilisateur $expediteur): static
    {
        $this->expediteur = $expediteur;
        return $this;
    }

    public function getDestinataire(): ?Utilisateur
    {
        return $this->destinataire;
    }

    public function setDestinataire(?Utilisateur $destinataire): static
    {
        $this->destinataire = $destinataire;
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

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(\DateTimeInterface $dateEnvoi): static
    {
        $this->dateEnvoi = $dateEnvoi;
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

    public function getType(): ?TypeMessage
    {
        return $this->type;
    }

    public function setType(TypeMessage $type): static
    {
        $this->type = $type;
        return $this;
    }
}
