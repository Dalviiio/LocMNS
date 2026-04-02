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

    public function findWithFilters(?string $search, ?string $etat, ?int $categorieId): array
    {
        $qb = $this->createQueryBuilder('m')
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
