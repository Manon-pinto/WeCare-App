<?php

namespace App\Entity;

use App\Repository\BeneficiaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BeneficiaireRepository::class)]
#[ORM\Table(name: 'beneficiaire')]
class Beneficiaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Utilisateur::class, inversedBy: 'beneficiaire')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Administrateur::class, inversedBy: 'beneficiaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Administrateur $adminCreateur = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7)]
    private ?string $longitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pathologie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $aidant = null;

    #[ORM\Column(length: 50)]
    private ?string $niveauRisque = null;

    #[ORM\OneToMany(mappedBy: 'beneficiaire', targetEntity: Intervention::class)]
    private Collection $interventions;

    #[ORM\OneToMany(mappedBy: 'beneficiaire', targetEntity: Message::class)]
    private Collection $messages;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->messages = new ArrayCollection();
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

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getPathologie(): ?string
    {
        return $this->pathologie;
    }

    public function setPathologie(?string $pathologie): static
    {
        $this->pathologie = $pathologie;
        return $this;
    }

    public function getAidant(): ?string
    {
        return $this->aidant;
    }

    public function setAidant(?string $aidant): static
    {
        $this->aidant = $aidant;
        return $this;
    }

    public function getNiveauRisque(): ?string
    {
        return $this->niveauRisque;
    }

    public function setNiveauRisque(string $niveauRisque): static
    {
        $this->niveauRisque = $niveauRisque;
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
            $intervention->setBeneficiaire($this);
        }
        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        $this->interventions->removeElement($intervention);
        return $this;
    }

    /** @return Collection<int, Message> */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setBeneficiaire($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): static
    {
        $this->messages->removeElement($message);
        return $this;
    }
}
