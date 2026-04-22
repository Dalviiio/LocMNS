<?php

namespace App\Entity;

enum StatutReservation: string
{
    case EnAttente = 'EnAttente';
    case Confirmee = 'Confirmee';
    case Annulee   = 'Annulee';

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
            self::EnAttente => 'bg-orange-100 text-orange-800',
            self::Confirmee => 'bg-green-100 text-green-800',
            self::Annulee   => 'bg-gray-100 text-gray-800',
        };
    }
}
