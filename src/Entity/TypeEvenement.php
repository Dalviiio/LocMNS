<?php

namespace App\Entity;

enum TypeEvenement: string
{
    case Panne              = 'panne';
    case Dysfonctionnement  = 'dysfonctionnement';
    case CableAbime         = 'cable_abime';
    case ProblemeConnexion  = 'probleme_connexion';
    case PerteAccessoire    = 'perte_accessoire';
    case Incompatibilite    = 'incompatibilite';
    case RetourAnticipe     = 'retour_anticipe';
    case Prolongation       = 'prolongation';
    case EchangeDemande     = 'echange_demande';
    case Complementaire     = 'complementaire';

    public function label(): string
    {
        return match($this) {
            self::Panne             => 'Panne',
            self::Dysfonctionnement => 'Dysfonctionnement',
            self::CableAbime        => 'Câble / connecteur abîmé',
            self::ProblemeConnexion => 'Problème de connexion',
            self::PerteAccessoire   => 'Perte d\'accessoire',
            self::Incompatibilite   => 'Incompatibilité matériel',
            self::RetourAnticipe    => 'Retour anticipé',
            self::Prolongation      => 'Prolongation',
            self::EchangeDemande    => 'Échange demandé',
            self::Complementaire    => 'Demande complémentaire',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Panne             => 'sb-danger',
            self::Dysfonctionnement => 'sb-warning',
            self::CableAbime        => 'sb-warning',
            self::ProblemeConnexion => 'sb-warning',
            self::PerteAccessoire   => 'sb-danger',
            self::Incompatibilite   => 'sb-gray',
            self::RetourAnticipe    => 'sb-success',
            self::Prolongation      => 'sb-info',
            self::EchangeDemande    => 'sb-info',
            self::Complementaire    => 'sb-gray',
        };
    }
}
