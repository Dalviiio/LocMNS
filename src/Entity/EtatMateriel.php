<?php

namespace App\Entity;

enum EtatMateriel: string
{
    case Neuf = 'Neuf';
    case Bon  = 'Bon';
    case Use  = 'Use';
    case Hs   = 'Hs';

    public function label(): string
    {
        return match($this) {
            self::Neuf => 'Neuf',
            self::Bon  => 'Bon',
            self::Use  => 'Usé',
            self::Hs   => 'Hors service',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Neuf => 'bg-green-100 text-green-800',
            self::Bon  => 'bg-blue-100 text-blue-800',
            self::Use  => 'bg-yellow-100 text-yellow-800',
            self::Hs   => 'bg-red-100 text-red-800',
        };
    }
}
