<?php

namespace App\Entity;

use App\Repository\MaterielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaterielRepository::class)]
class Materiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(length: 100, unique: true, nullable: true)]
    private ?string $numeroSerie = null;

    #[ORM\Column(type: 'string', enumType: EtatMateriel::class)]
    private EtatMateriel $etat = EtatMateriel::Bon;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $localisation = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: 'materiels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(targetEntity: Emprunt::class, mappedBy: 'materiel')]
    private Collection $emprunts;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'materiel')]
    private Collection $reservations;

    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'materiel', cascade: ['remove'])]
    private Collection $documents;

    public function __construct()
    {
        $this->createdAt    = new \DateTime();
        $this->emprunts     = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->documents    = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getNumeroSerie(): ?string { return $this->numeroSerie; }
    public function setNumeroSerie(?string $numeroSerie): static { $this->numeroSerie = $numeroSerie; return $this; }

    public function getEtat(): EtatMateriel { return $this->etat; }
    public function setEtat(EtatMateriel $etat): static { $this->etat = $etat; return $this; }

    public function getLocalisation(): ?string { return $this->localisation; }
    public function setLocalisation(?string $localisation): static { $this->localisation = $localisation; return $this; }

    public function getCategorie(): ?Categorie { return $this->categorie; }
    public function setCategorie(?Categorie $categorie): static { $this->categorie = $categorie; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getEmprunts(): Collection { return $this->emprunts; }
    public function getReservations(): Collection { return $this->reservations; }
    public function getDocuments(): Collection { return $this->documents; }

    public function isDisponible(): bool
    {
        foreach ($this->emprunts as $emprunt) {
            if ($emprunt->getStatut() === StatutEmprunt::EnCours) {
                return false;
            }
        }
        return $this->etat !== EtatMateriel::HS;
    }
}
