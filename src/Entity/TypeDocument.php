<?php

namespace App\Entity;

enum TypeDocument: string
{
    case Notice       = 'notice';
    case DocTechnique = 'doc_technique';
    case Video        = 'video';

    public function label(): string
    {
        return match($this) {
            self::Notice       => 'Notice',
            self::DocTechnique => 'Doc technique',
            self::Video        => 'Vidéo',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Notice       => 'sb-info',
            self::DocTechnique => 'sb-gray',
            self::Video        => 'sb-warning',
        };
    }
}
