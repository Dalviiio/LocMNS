<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function findByProfil(int $profilId): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.profil = :profil')
            ->setParameter('profil', $profilId)
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
