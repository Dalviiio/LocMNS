<?php

namespace App\Entity;

enum StatutEmprunt: string
{
    case EnCours = 'en_cours';
    case Rendu   = 'rendu';
    case Retard  = 'retard';

    public function label(): string
    {
        return match($this) {
            self::EnCours => 'En cours',
            self::Rendu   => 'Rendu',
            self::Retard  => 'Retard',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::EnCours => 'sb-info',
            self::Rendu   => 'sb-success',
            self::Retard  => 'sb-danger',
        };
    }
}
