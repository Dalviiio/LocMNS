<?php

namespace App\Service;

use App\Entity\Alerte;
use App\Entity\Emprunt;
use App\Entity\TypeAlerte;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class AlerteService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function creer(
        Utilisateur $user,
        TypeAlerte $type,
        string $message,
        ?Emprunt $emprunt = null
    ): Alerte {
        $alerte = new Alerte();
        $alerte->setUtilisateur($user);
        $alerte->setType($type);
        $alerte->setMessage($message);
        $alerte->setEmprunt($emprunt);

        $this->em->persist($alerte);

        return $alerte;
    }
}
