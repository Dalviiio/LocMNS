<?php

namespace App\Entity;

enum TypeDocument: string
{
    case Notice       = 'Notice';
    case DocTechnique = 'DocTechnique';
    case Video        = 'Video';

    public function label(): string
    {
        return match($this) {
            self::Notice       => 'Notice',
            self::DocTechnique => 'Doc technique',
            self::Video        => 'Vidéo',
        };
    }
}
