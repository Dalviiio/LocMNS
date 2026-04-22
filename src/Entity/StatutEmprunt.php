<?php

namespace App\Entity;

enum StatutEmprunt: string
{
    case EnCours = 'EnCours';
    case Rendu   = 'Rendu';
    case Retard  = 'Retard';

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
            self::EnCours => 'bg-blue-100 text-blue-800',
            self::Rendu   => 'bg-green-100 text-green-800',
            self::Retard  => 'bg-red-100 text-red-800',
        };
    }
}
