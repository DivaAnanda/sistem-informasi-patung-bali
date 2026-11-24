<?php

namespace App\Enums;

enum StatueStatus: string
{
    case Published = 'published';
    case Draft = 'draft';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Published => __('Terbit'),
            self::Draft => __('Draf'),
            self::Hidden => __('Disembunyikan'),
        };
    }
}
