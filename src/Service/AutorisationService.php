<?php

namespace App\Service;

use App\Entity\Materiel;
use App\Entity\Utilisateur;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AutorisationService
{
    private const PROFILS_LIBRES = ['Administrateur', 'Gestionnaire'];

    public function __construct(private EntityManagerInterface $em) {}

    public function peutEmprunter(Utilisateur $user, Materiel $materiel): bool
    {
        if (in_array($user->getProfil()->getNom(), self::PROFILS_LIBRES, true)) {
            return true;
        }

        $categoriesAutorisees = $this->getCategoriesAutorisees($user);
        foreach ($categoriesAutorisees as $cat) {
            if ($cat->getId() === $materiel->getCategorie()->getId()) {
                return true;
            }
        }

        return false;
    }

    public function getCategoriesAutorisees(Utilisateur $user): Collection
    {
        return $user->getProfil()->getCategories();
    }

    public function verifierOuRefuser(Utilisateur $user, Materiel $materiel): void
    {
        if (!$this->peutEmprunter($user, $materiel)) {
            throw new AccessDeniedHttpException('Ce matériel n\'est pas accessible avec votre profil.');
        }
    }
}
