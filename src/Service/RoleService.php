<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleService
{
    public function verifier(
        Utilisateur $user,
        array $profilsAutorises,
        string $message = 'Accès non autorisé.'
    ): void {
        if (!in_array($user->getProfil()->getNom(), $profilsAutorises, true)) {
            throw new AccessDeniedHttpException($message);
        }
    }
}
