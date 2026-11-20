<?php

namespace Database\Seeders;

use App\Models\JunctionType;
use App\Models\Regency;
use App\Models\StatueType;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    private const REGENCIES = [
        'badung' => 'Badung',
        'bangli' => 'Bangli',
        'buleleng' => 'Buleleng',
        'denpasar' => 'Kota Denpasar',
        'gianyar' => 'Gianyar',
        'jembrana' => 'Jembrana',
        'karangasem' => 'Karangasem',
        'klungkung' => 'Klungkung',
        'tabanan' => 'Tabanan',
    ];

    // Main types are mutually exclusive; "Ikonik" is a separate yes/no flag on top of them.
    private const STATUE_TYPES = [
        'religius' => ['id' => 'Religius', 'en' => 'Religious'],
        'budaya' => ['id' => 'Budaya', 'en' => 'Cultural'],
        'dekoratif' => ['id' => 'Dekoratif', 'en' => 'Decorative'],
    ];

    private const JUNCTION_TYPES = [
        'perempatan' => ['id' => 'Perempatan', 'en' => 'Crossroads'],
        'pertigaan' => ['id' => 'Pertigaan', 'en' => 'T-junction'],
        'bundaran' => ['id' => 'Bundaran', 'en' => 'Roundabout'],
        'simpang' => ['id' => 'Simpang banyak', 'en' => 'Multi-way junction'],
        'pinggir-jalan' => ['id' => 'Pinggir jalan', 'en' => 'Roadside'],
        'dalam-kawasan' => ['id' => 'Di dalam kawasan', 'en' => 'Inside a site'],
    ];

    public function run(): void
    {
        foreach (self::REGENCIES as $slug => $name) {
            Regency::query()->updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $this->seedTranslated(StatueType::class, self::STATUE_TYPES);
        $this->seedTranslated(JunctionType::class, self::JUNCTION_TYPES);
    }

    /**
     * @param  class-string<StatueType|JunctionType>  $model
     * @param  array<string, array{id: string, en: string}>  $items
     */
    private function seedTranslated(string $model, array $items): void
    {
        $order = 0;

        foreach ($items as $slug => $name) {
            $model::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'sort_order' => ++$order]);
        }
    }
}
