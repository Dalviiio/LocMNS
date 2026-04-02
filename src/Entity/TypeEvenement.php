<?php

namespace App\Entity;

enum TypeEvenement: string
{
    case Panne           = 'panne';
    case Dysfonctionnement = 'dysfonctionnement';
    case RetourAnticipe  = 'retour_anticipe';
    case Prolongation    = 'prolongation';
    case Complementaire  = 'complementaire';

    public function label(): string
    {
        return match($this) {
            self::Panne             => 'Panne',
            self::Dysfonctionnement => 'Dysfonctionnement',
            self::RetourAnticipe    => 'Retour anticipé',
            self::Prolongation      => 'Prolongation',
            self::Complementaire    => 'Demande complémentaire',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Panne             => 'sb-danger',
            self::Dysfonctionnement => 'sb-warning',
            self::RetourAnticipe    => 'sb-success',
            self::Prolongation      => 'sb-info',
            self::Complementaire    => 'sb-gray',
        };
    }
}
