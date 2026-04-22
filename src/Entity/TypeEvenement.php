<?php

namespace App\Entity;

enum TypeEvenement: string
{
    case Panne                 = 'Panne';
    case Dysfonctionnement     = 'Dysfonctionnement';
    case RetourAnticipe        = 'RetourAnticipe';
    case Prolongation          = 'Prolongation';
    case DemandeComplementaire = 'DemandeComplementaire';

    public function label(): string
    {
        return match($this) {
            self::Panne                 => 'Panne',
            self::Dysfonctionnement     => 'Dysfonctionnement',
            self::RetourAnticipe        => 'Retour anticipé',
            self::Prolongation          => 'Prolongation',
            self::DemandeComplementaire => 'Demande complémentaire',
        };
    }
}
