<?php

namespace App\Repository;

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

    public function findWithFilters(
        ?string $search = null,
        ?string $etat = null,
        ?int $categorieId = null,
        array $categorieIds = []
    ): array {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.categorie', 'c')->addSelect('c')
            ->orderBy('m.nom', 'ASC');

        if ($search) {
            $qb->andWhere('m.nom LIKE :s OR m.numeroSerie LIKE :s OR m.localisation LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }
        if ($etat) {
            $qb->andWhere('m.etat = :etat')->setParameter('etat', $etat);
        }
        if ($categorieId) {
            $qb->andWhere('c.id = :cat')->setParameter('cat', $categorieId);
        }
        if (!empty($categorieIds)) {
            $qb->andWhere('c.id IN (:cats)')->setParameter('cats', $categorieIds);
        }

        return $qb->getQuery()->getResult();
    }

    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()->getSingleScalarResult();
    }

    public function countEmpruntes(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(DISTINCT m.id)')
            ->innerJoin('m.emprunts', 'e')
            ->andWhere('e.statut = :s')
            ->setParameter('s', StatutEmprunt::EnCours)
            ->getQuery()->getSingleScalarResult();
    }

    public function countDisponibles(): int
    {
        return $this->countTotal() - $this->countEmpruntes();
    }
}
