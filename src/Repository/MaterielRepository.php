<?php

namespace App\Repository;

use App\Entity\EtatMateriel;
use App\Entity\Materiel;
use App\Entity\StatutEmprunt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaterielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Materiel::class);
    }

    public function countTotal(): int
    {
        return $this->count([]);
    }

    public function countDisponibles(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->leftJoin('m.emprunts', 'e', 'WITH', 'e.statut = :statut')
            ->setParameter('statut', StatutEmprunt::EnCours->value)
            ->where('e.id IS NULL')
            ->andWhere('m.etat != :hs')
            ->setParameter('hs', EtatMateriel::HS->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countEmpruntes(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(DISTINCT m.id)')
            ->join('m.emprunts', 'e')
            ->where('e.statut = :statut')
            ->setParameter('statut', StatutEmprunt::EnCours->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countIndisponibles(): int
    {
        $empruntes = $this->countEmpruntes();
        $hs = $this->count(['etat' => EtatMateriel::HS]);
        return $empruntes + $hs;
    }

    /**
     * Retourne les matériels non-HS disponibles ou non, groupés par nom pour la vue catalogue client.
     * Format : [['nom'=>, 'categorie'=>, 'categorie_id'=>, 'dispo'=>, 'total'=>, 'sample_id'=>], ...]
     */
    public function findGroupedPourClient(array $categorieIds = []): array
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('c')
            ->join('m.categorie', 'c')
            ->leftJoin('m.emprunts', 'e', 'WITH', 'e.statut = :statut')
            ->setParameter('statut', StatutEmprunt::EnCours->value)
            ->where('m.etat != :hs')
            ->setParameter('hs', EtatMateriel::HS->value)
            ->orderBy('m.nom', 'ASC');

        if (!empty($categorieIds)) {
            $qb->andWhere('c.id IN (:cats)')->setParameter('cats', $categorieIds);
        }

        $materiels = $qb->getQuery()->getResult();

        $grouped = [];
        foreach ($materiels as $m) {
            $key = $m->getNom() . '||' . $m->getCategorie()->getId();
            $disponible = $m->isDisponible();
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'nom'          => $m->getNom(),
                    'categorie'    => $m->getCategorie()->getNom(),
                    'categorie_id' => $m->getCategorie()->getId(),
                    'dispo'        => 0,
                    'total'        => 0,
                    'sample_id'    => $m->getId(),
                ];
            }
            $grouped[$key]['total']++;
            if ($disponible) {
                $grouped[$key]['dispo']++;
                $grouped[$key]['sample_id'] = $m->getId();
            }
        }

        return array_values($grouped);
    }

    public function findDisponiblesParNom(string $nom, array $categorieIds = [], int $limite = 10): array
    {
        $qb = $this->createQueryBuilder('m')
            ->join('m.categorie', 'c')
            ->leftJoin('m.emprunts', 'e', 'WITH', 'e.statut = :statut')
            ->setParameter('statut', StatutEmprunt::EnCours->value)
            ->where('m.nom = :nom')
            ->setParameter('nom', $nom)
            ->andWhere('m.etat != :hs')
            ->setParameter('hs', EtatMateriel::HS->value)
            ->andWhere('e.id IS NULL')
            ->setMaxResults($limite);

        if (!empty($categorieIds)) {
            $qb->andWhere('c.id IN (:cats)')->setParameter('cats', $categorieIds);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByCategories(array $categorieIds): array
    {
        if (empty($categorieIds)) return $this->findAll();

        return $this->createQueryBuilder('m')
            ->addSelect('c')
            ->join('m.categorie', 'c')
            ->where('c.id IN (:cats)')
            ->setParameter('cats', $categorieIds)
            ->orderBy('m.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithFilters(?string $search, ?string $etat, ?int $categorieId): array
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('c')
            ->join('m.categorie', 'c')
            ->orderBy('m.nom', 'ASC');

        if ($search) {
            $qb->andWhere('m.nom LIKE :search OR m.numeroSerie LIKE :search OR m.localisation LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        if ($etat) {
            $qb->andWhere('m.etat = :etat')->setParameter('etat', $etat);
        }
        if ($categorieId) {
            $qb->andWhere('m.categorie = :cat')->setParameter('cat', $categorieId);
        }

        return $qb->getQuery()->getResult();
    }
}
