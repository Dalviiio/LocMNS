<?php

namespace App\Entity;

enum TypeAlerte: string
{
    case Retard          = 'Retard';
    case NouvelleDemande = 'NouvelleDemande';
    case Panne           = 'Panne';

    public function label(): string
    {
        return match($this) {
            self::Retard          => 'Retard',
            self::NouvelleDemande => 'Nouvelle demande',
            self::Panne           => 'Panne',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Retard          => 'bg-red-100 text-red-800',
            self::NouvelleDemande => 'bg-blue-100 text-blue-800',
            self::Panne           => 'bg-orange-100 text-orange-800',
        };
    }
}
