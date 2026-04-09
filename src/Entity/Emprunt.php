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
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'emprunts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Materiel::class, inversedBy: 'emprunts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Materiel $materiel = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateFinPrevue = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateRetour = null;

    #[ORM\Column(type: 'string', enumType: StatutEmprunt::class)]
    private StatutEmprunt $statut = StatutEmprunt::EnCours;

    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'emprunt', cascade: ['remove'])]
    private Collection $evenements;

    #[ORM\OneToMany(targetEntity: Alerte::class, mappedBy: 'emprunt')]
    private Collection $alertes;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'empruntsAccessoires')]
    #[ORM\JoinColumn(name: 'parent_id', nullable: true, onDelete: 'SET NULL')]
    private ?Emprunt $parentEmprunt = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentEmprunt')]
    private Collection $empruntsAccessoires;

    public function __construct()
    {
        $this->evenements          = new ArrayCollection();
        $this->alertes             = new ArrayCollection();
        $this->empruntsAccessoires = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): static { $this->utilisateur = $utilisateur; return $this; }

    public function getMateriel(): ?Materiel { return $this->materiel; }
    public function setMateriel(?Materiel $materiel): static { $this->materiel = $materiel; return $this; }

    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(\DateTimeInterface $dateDebut): static { $this->dateDebut = $dateDebut; return $this; }

    public function getDateFinPrevue(): ?\DateTimeInterface { return $this->dateFinPrevue; }
    public function setDateFinPrevue(\DateTimeInterface $dateFinPrevue): static { $this->dateFinPrevue = $dateFinPrevue; return $this; }

    public function getDateRetour(): ?\DateTimeInterface { return $this->dateRetour; }
    public function setDateRetour(?\DateTimeInterface $dateRetour): static { $this->dateRetour = $dateRetour; return $this; }

    public function getStatut(): StatutEmprunt { return $this->statut; }
    public function setStatut(StatutEmprunt $statut): static { $this->statut = $statut; return $this; }

    public function getEvenements(): Collection { return $this->evenements; }
    public function getAlertes(): Collection { return $this->alertes; }

    public function getParentEmprunt(): ?Emprunt { return $this->parentEmprunt; }
    public function setParentEmprunt(?Emprunt $parentEmprunt): static { $this->parentEmprunt = $parentEmprunt; return $this; }
    public function getEmpruntsAccessoires(): Collection { return $this->empruntsAccessoires; }

    public function isEnRetard(): bool
    {
        return $this->statut === StatutEmprunt::EnCours
            && $this->dateFinPrevue < new \DateTime();
    }

    public function updateStatutRetard(): void
    {
        if ($this->isEnRetard()) {
            $this->statut = StatutEmprunt::Retard;
        }
    }
}
