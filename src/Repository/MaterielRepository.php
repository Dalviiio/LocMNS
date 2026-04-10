<?php

namespace App\Repository;

use App\Entity\EtatMateriel;
use App\Entity\Materiel;
use App\Entity\Reservation;
use App\Entity\StatutEmprunt;
use App\Entity\StatutReservation;
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

    /**
     * Catalogue pour le formulaire réservation : un groupe par modèle.
     * Retourne [nom, categorie, categorie_id, total, sample_id]
     */
    public function findGroupedPourReservation(array $categorieIds = []): array
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('c')
            ->join('m.categorie', 'c')
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
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'nom'          => $m->getNom(),
                    'categorie'    => $m->getCategorie()->getNom(),
                    'categorie_id' => $m->getCategorie()->getId(),
                    'total'        => 0,
                    'sample_id'    => $m->getId(),
                ];
            }
            $grouped[$key]['total']++;
        }

        return array_values($grouped);
    }

    /**
     * Unités d'un modèle n'ayant pas de réservation confirmée chevauchant la période.
     */
    public function findDisponiblesParNomPourPeriode(
        string $nom,
        \DateTimeInterface $debut,
        \DateTimeInterface $fin,
        array $categorieIds = [],
        int $limite = 20
    ): array {
        // Sous-requête : IDs des matériels déjà réservés (confirmée / approuvée / en cours) sur la période
        $subDql = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(r2.materiel)')
            ->from(Reservation::class, 'r2')
            ->where('r2.statut IN (:blocking)')
            ->andWhere('r2.dateDebut < :fin')
            ->andWhere('r2.dateFin > :debut')
            ->getDQL();

        $qb = $this->createQueryBuilder('m')
            ->join('m.categorie', 'c')
            ->where('m.nom = :nom')
            ->andWhere('m.etat != :hs')
            ->andWhere('m.id NOT IN (' . $subDql . ')')
            ->setParameter('nom', $nom)
            ->setParameter('hs', EtatMateriel::HS->value)
            ->setParameter('blocking', [
                StatutReservation::Confirmee->value,
                StatutReservation::Approuvee->value,
                StatutReservation::EnCours->value,
            ])
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
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

    /**
     * @param array $categorieIdsAutorises Quand non vide, restreint aux catégories autorisées (profil limité)
     */
    public function countWithFilters(?string $search, ?string $etat, ?int $categorieId, array $categorieIdsAutorises = []): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->join('m.categorie', 'c');

        if (!empty($categorieIdsAutorises)) {
            $qb->andWhere('c.id IN (:autorises)')->setParameter('autorises', $categorieIdsAutorises);
        }
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

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findWithFilters(?string $search, ?string $etat, ?int $categorieId, array $categorieIdsAutorises = [], ?int $limit = null, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('c')
            ->join('m.categorie', 'c')
            ->orderBy('m.nom', 'ASC');

        if (!empty($categorieIdsAutorises)) {
            $qb->andWhere('c.id IN (:autorises)')->setParameter('autorises', $categorieIdsAutorises);
        }
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
        if ($limit !== null) {
            $qb->setMaxResults($limit)->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }
}
