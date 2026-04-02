<?php

namespace App\Entity;

use App\Repository\AlerteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlerteRepository::class)]
class Alerte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'alertes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Emprunt::class, inversedBy: 'alertes')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Emprunt $emprunt = null;

    #[ORM\Column(type: 'string', enumType: TypeAlerte::class)]
    private ?TypeAlerte $type = null;

    #[ORM\Column(type: 'text')]
    private ?string $message = null;

    #[ORM\Column(type: 'boolean')]
    private bool $lu = false;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): static { $this->utilisateur = $utilisateur; return $this; }

    public function getEmprunt(): ?Emprunt { return $this->emprunt; }
    public function setEmprunt(?Emprunt $emprunt): static { $this->emprunt = $emprunt; return $this; }

    public function getType(): ?TypeAlerte { return $this->type; }
    public function setType(TypeAlerte $type): static { $this->type = $type; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(string $message): static { $this->message = $message; return $this; }

    public function isLu(): bool { return $this->lu; }
    public function setLu(bool $lu): static { $this->lu = $lu; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
}
