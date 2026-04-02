<?php

namespace App\Entity;

enum EtatMateriel: string
{
    case Neuf = 'neuf';
    case Bon  = 'bon';
    case Use  = 'use';
    case HS   = 'hs';

    public function label(): string
    {
        return match($this) {
            self::Neuf => 'Neuf',
            self::Bon  => 'Bon',
            self::Use  => 'Usé',
            self::HS   => 'Hors service',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Neuf => 'sb-success',
            self::Bon  => 'sb-info',
            self::Use  => 'sb-warning',
            self::HS   => 'sb-danger',
        };
    }
}
