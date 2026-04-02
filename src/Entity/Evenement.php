<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Emprunt::class, inversedBy: 'evenements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emprunt $emprunt = null;

    #[ORM\Column(type: 'string', enumType: TypeEvenement::class)]
    private ?TypeEvenement $type = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmprunt(): ?Emprunt { return $this->emprunt; }
    public function setEmprunt(?Emprunt $emprunt): static { $this->emprunt = $emprunt; return $this; }

    public function getType(): ?TypeEvenement { return $this->type; }
    public function setType(TypeEvenement $type): static { $this->type = $type; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
}
