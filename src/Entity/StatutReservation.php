<?php

namespace App\Entity;

enum StatutReservation: string
{
    case EnAttente  = 'en_attente';
    case Approuvee  = 'approuvee';
    case Confirmee  = 'confirmee';
    case EnCours    = 'en_cours';
    case Terminee   = 'terminee';
    case Annulee    = 'annulee';
    case Refusee    = 'refusee';
    case EnRetard   = 'en_retard';
    case EnLitige   = 'en_litige';
    case Expiree    = 'expiree';

    public function label(): string
    {
        return match($this) {
            self::EnAttente => 'En attente',
            self::Approuvee => 'Approuvée',
            self::Confirmee => 'Confirmée',
            self::EnCours   => 'En cours',
            self::Terminee  => 'Terminée',
            self::Annulee   => 'Annulée',
            self::Refusee   => 'Refusée',
            self::EnRetard  => 'En retard',
            self::EnLitige  => 'En litige',
            self::Expiree   => 'Expirée',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::EnAttente => 'sb-warning',
            self::Approuvee => 'sb-info',
            self::Confirmee => 'sb-success',
            self::EnCours   => 'sb-success',
            self::Terminee  => 'sb-gray',
            self::Annulee   => 'sb-gray',
            self::Refusee   => 'sb-danger',
            self::EnRetard  => 'sb-danger',
            self::EnLitige  => 'sb-danger',
            self::Expiree   => 'sb-gray',
        };
    }
}
