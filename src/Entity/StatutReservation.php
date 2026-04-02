<?php

namespace App\Entity;

enum StatutReservation: string
{
    case EnAttente  = 'en_attente';
    case Confirmee  = 'confirmee';
    case Annulee    = 'annulee';

    public function label(): string
    {
        return match($this) {
            self::EnAttente => 'En attente',
            self::Confirmee => 'Confirmée',
            self::Annulee   => 'Annulée',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::EnAttente => 'sb-warning',
            self::Confirmee => 'sb-success',
            self::Annulee   => 'sb-gray',
        };
    }
}
