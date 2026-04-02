<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 150, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $motDePasse = null;

    #[ORM\ManyToOne(targetEntity: Profil::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Profil $profil = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(targetEntity: Emprunt::class, mappedBy: 'utilisateur')]
    private Collection $emprunts;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'utilisateur')]
    private Collection $reservations;

    #[ORM\OneToMany(targetEntity: Alerte::class, mappedBy: 'utilisateur')]
    private Collection $alertes;

    public function __construct()
    {
        $this->createdAt    = new \DateTime();
        $this->emprunts     = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->alertes      = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getNomComplet(): string { return $this->prenom . ' ' . $this->nom; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getMotDePasse(): ?string { return $this->motDePasse; }
    public function setMotDePasse(string $motDePasse): static { $this->motDePasse = $motDePasse; return $this; }

    public function getProfil(): ?Profil { return $this->profil; }
    public function setProfil(?Profil $profil): static { $this->profil = $profil; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getEmprunts(): Collection { return $this->emprunts; }
    public function getReservations(): Collection { return $this->reservations; }
    public function getAlertes(): Collection { return $this->alertes; }

    public function getEmpruntsActifs(): Collection
    {
        return $this->emprunts->filter(fn(Emprunt $e) => $e->getStatut() === StatutEmprunt::EnCours);
    }
}
