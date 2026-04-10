<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Materiel::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Materiel $materiel = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'string', enumType: StatutReservation::class)]
    private StatutReservation $statut = StatutReservation::EnAttente;

    public function getId(): ?int { return $this->id; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): static { $this->utilisateur = $utilisateur; return $this; }

    public function getMateriel(): ?Materiel { return $this->materiel; }
    public function setMateriel(?Materiel $materiel): static { $this->materiel = $materiel; return $this; }

    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(\DateTimeInterface $dateDebut): static { $this->dateDebut = $dateDebut; return $this; }

    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(\DateTimeInterface $dateFin): static { $this->dateFin = $dateFin; return $this; }

    public function getStatut(): StatutReservation { return $this->statut; }
    public function setStatut(StatutReservation $statut): static { $this->statut = $statut; return $this; }
}
