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
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $nom;

    #[ORM\Column(length: 100, unique: true, nullable: true)]
    private ?string $numeroSerie = null;

    #[ORM\Column(type: 'string', enumType: EtatMateriel::class)]
    private EtatMateriel $etat;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $localisation = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: 'materiels')]
    #[ORM\JoinColumn(nullable: false)]
    private Categorie $categorie;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'utilisePour')]
    #[ORM\JoinTable(
        name: 'materiel_accessoire',
        joinColumns: [new ORM\JoinColumn(name: 'materiel_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'accessoire_id', referencedColumnName: 'id')]
    )]
    private Collection $accessoires;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'accessoires')]
    private Collection $utilisePour;

    #[ORM\OneToMany(mappedBy: 'materiel', targetEntity: Emprunt::class)]
    private Collection $emprunts;

    #[ORM\OneToMany(mappedBy: 'materiel', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'materiel', targetEntity: Document::class)]
    private Collection $documents;

    public function __construct()
    {
        $this->createdAt    = new \DateTime();
        $this->accessoires  = new ArrayCollection();
        $this->utilisePour  = new ArrayCollection();
        $this->emprunts     = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->documents    = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getNumeroSerie(): ?string { return $this->numeroSerie; }
    public function setNumeroSerie(?string $numeroSerie): static { $this->numeroSerie = $numeroSerie; return $this; }

    public function getEtat(): EtatMateriel { return $this->etat; }
    public function setEtat(EtatMateriel $etat): static { $this->etat = $etat; return $this; }

    public function getLocalisation(): ?string { return $this->localisation; }
    public function setLocalisation(?string $localisation): static { $this->localisation = $localisation; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getCategorie(): Categorie { return $this->categorie; }
    public function setCategorie(Categorie $categorie): static { $this->categorie = $categorie; return $this; }

    public function getAccessoires(): Collection { return $this->accessoires; }
    public function addAccessoire(Materiel $acc): static { if (!$this->accessoires->contains($acc)) { $this->accessoires->add($acc); } return $this; }
    public function removeAccessoire(Materiel $acc): static { $this->accessoires->removeElement($acc); return $this; }

    public function getUtilisePour(): Collection { return $this->utilisePour; }

    public function getEmprunts(): Collection { return $this->emprunts; }
    public function getReservations(): Collection { return $this->reservations; }
    public function getDocuments(): Collection { return $this->documents; }
}
