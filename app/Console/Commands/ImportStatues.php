<?php

namespace App\Console\Commands;

use App\Services\StatueImport\StatueImporter;
use App\Services\StatueImport\StatueImportException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('patung:import {file? : Path ke file CSV (bawaan: database/data/statues.csv)}')]
#[Description('Impor data patung dari CSV ke database')]
class ImportStatues extends Command
{
    public function handle(StatueImporter $importer): int
    {
        $path = $this->argument('file') ?? database_path('data/statues.csv');

        try {
            $result = $importer->import($path);
        } catch (StatueImportException $exception) {
            $this->components->error($exception->getMessage().' Tidak ada data yang ditulis.');
            $this->components->bulletList($exception->errors);

            return self::FAILURE;
        }

        $this->components->info("{$result['imported']} patung berhasil diimpor dari {$path}.");

        if ($result['removed'] > 0) {
            $this->components->warn("{$result['removed']} patung dihapus karena tidak ada lagi di CSV.");
        }

        return self::SUCCESS;
    }
}
