<?php

namespace App\Console\Commands;

use App\Support\StatuePhotos;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('patung:thumbnails {--force : Buat ulang semua thumbnail}')]
#[Description('Buat foto ukuran kecil untuk kartu dan peta dari public/images/patung')]
class MakeThumbnails extends Command
{
    public function handle(): int
    {
        $created = 0;

        foreach (glob(StatuePhotos::directory().'/*.jpg') ?: [] as $source) {
            $target = StatuePhotos::thumbnailPath(basename($source));

            if (! $this->option('force') && is_file($target) && filemtime($target) >= filemtime($source)) {
                continue;
            }

            StatuePhotos::makeThumbnail($source, $target);
            $created++;
        }

        $this->components->info("{$created} thumbnail dibuat di ".StatuePhotos::thumbnailDirectory().'.');

        return self::SUCCESS;
    }
}
