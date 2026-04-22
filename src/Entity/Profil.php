<?php

namespace App\Entity;

use App\Repository\ProfilRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfilRepository::class)]
class Profil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $nom;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Categorie::class)]
    #[ORM\JoinTable(
        name: 'profil_categorie',
        joinColumns: [new ORM\JoinColumn(name: 'id_profil', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'id_categorie', referencedColumnName: 'id')]
    )]
    private Collection $categories;

    #[ORM\OneToMany(mappedBy: 'profil', targetEntity: Utilisateur::class)]
    private Collection $utilisateurs;

    public function __construct()
    {
        $this->categories   = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getCategories(): Collection { return $this->categories; }
    public function addCategorie(Categorie $cat): static { if (!$this->categories->contains($cat)) { $this->categories->add($cat); } return $this; }
    public function removeCategorie(Categorie $cat): static { $this->categories->removeElement($cat); return $this; }

    public function getUtilisateurs(): Collection { return $this->utilisateurs; }
}
