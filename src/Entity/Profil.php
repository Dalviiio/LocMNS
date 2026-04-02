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
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'profil')]
    private Collection $utilisateurs;

    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'profils')]
    #[ORM\JoinTable(name: 'profil_categorie')]
    private Collection $categories;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
        $this->categories   = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getUtilisateurs(): Collection { return $this->utilisateurs; }

    public function getCategories(): Collection { return $this->categories; }
    public function addCategorie(Categorie $categorie): static
    {
        if (!$this->categories->contains($categorie)) {
            $this->categories->add($categorie);
        }
        return $this;
    }
    public function removeCategorie(Categorie $categorie): static
    {
        $this->categories->removeElement($categorie);
        return $this;
    }
}
