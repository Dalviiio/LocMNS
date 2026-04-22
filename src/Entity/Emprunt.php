<?php

namespace App\Entity;

use App\Repository\EmpruntRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmpruntRepository::class)]
class Emprunt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateFinPrevue;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateRetour = null;

    #[ORM\Column(type: 'string', enumType: StatutEmprunt::class)]
    private StatutEmprunt $statut;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'emprunts')]
    #[ORM\JoinColumn(nullable: false)]
    private Utilisateur $utilisateur;

    #[ORM\ManyToOne(targetEntity: Materiel::class, inversedBy: 'emprunts')]
    #[ORM\JoinColumn(nullable: false)]
    private Materiel $materiel;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'empruntsAccessoires')]
    #[ORM\JoinColumn(name: 'parent_id', nullable: true)]
    private ?Emprunt $parentEmprunt = null;

    #[ORM\OneToMany(mappedBy: 'parentEmprunt', targetEntity: self::class)]
    private Collection $empruntsAccessoires;

    #[ORM\OneToMany(mappedBy: 'emprunt', targetEntity: Evenement::class)]
    private Collection $evenements;

    #[ORM\OneToMany(mappedBy: 'emprunt', targetEntity: Alerte::class)]
    private Collection $alertes;

    public function __construct()
    {
        $this->createdAt           = new \DateTime();
        $this->empruntsAccessoires = new ArrayCollection();
        $this->evenements          = new ArrayCollection();
        $this->alertes             = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getDateDebut(): \DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(\DateTimeInterface $dateDebut): static { $this->dateDebut = $dateDebut; return $this; }

    public function getDateFinPrevue(): \DateTimeInterface { return $this->dateFinPrevue; }
    public function setDateFinPrevue(\DateTimeInterface $dateFinPrevue): static { $this->dateFinPrevue = $dateFinPrevue; return $this; }

    public function getDateRetour(): ?\DateTimeInterface { return $this->dateRetour; }
    public function setDateRetour(?\DateTimeInterface $dateRetour): static { $this->dateRetour = $dateRetour; return $this; }

    public function getStatut(): StatutEmprunt { return $this->statut; }
    public function setStatut(StatutEmprunt $statut): static { $this->statut = $statut; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getUtilisateur(): Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(Utilisateur $utilisateur): static { $this->utilisateur = $utilisateur; return $this; }

    public function getMateriel(): Materiel { return $this->materiel; }
    public function setMateriel(Materiel $materiel): static { $this->materiel = $materiel; return $this; }

    public function getParentEmprunt(): ?Emprunt { return $this->parentEmprunt; }
    public function setParentEmprunt(?Emprunt $parentEmprunt): static { $this->parentEmprunt = $parentEmprunt; return $this; }

    public function getEmpruntsAccessoires(): Collection { return $this->empruntsAccessoires; }
    public function getEvenements(): Collection { return $this->evenements; }
    public function getAlertes(): Collection { return $this->alertes; }

    public function isEnRetard(): bool
    {
        return $this->statut !== StatutEmprunt::Rendu
            && $this->dateFinPrevue < new \DateTime();
    }
}
