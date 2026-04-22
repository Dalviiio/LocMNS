<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: TypeDocument::class)]
    private TypeDocument $type;

    #[ORM\Column(length: 200)]
    private string $titre;

    #[ORM\Column(length: 500)]
    private string $url;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Materiel::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private Materiel $materiel;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getType(): TypeDocument { return $this->type; }
    public function setType(TypeDocument $type): static { $this->type = $type; return $this; }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getUrl(): string { return $this->url; }
    public function setUrl(string $url): static { $this->url = $url; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getMateriel(): Materiel { return $this->materiel; }
    public function setMateriel(Materiel $materiel): static { $this->materiel = $materiel; return $this; }
}
