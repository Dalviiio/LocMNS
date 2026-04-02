<?php

namespace App\Entity;

enum TypeAlerte: string
{
    case Retard         = 'retard';
    case NouvelleDemande = 'nouvelle_demande';
    case Panne          = 'panne';

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
            self::Retard          => 'sb-danger',
            self::NouvelleDemande => 'sb-info',
            self::Panne           => 'sb-warning',
        };
    }
}
