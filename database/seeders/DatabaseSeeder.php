<?php

namespace Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ReferenceDataSeeder::class);

        $exitCode = Artisan::call('patung:import', [], $this->command?->getOutput());

        if ($exitCode !== Command::SUCCESS) {
            throw new RuntimeException('Impor data patung gagal. Lihat daftar masalah di atas.');
        }
    }
}
