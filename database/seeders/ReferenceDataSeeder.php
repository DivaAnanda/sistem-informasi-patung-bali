<?php

namespace Database\Seeders;

use App\Models\JunctionType;
use App\Models\Regency;
use App\Models\Theme;
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

    // Themes of landmark statues, from the interview with Dewa Gede Purwita (20 November 2026).
    private const THEMES = [
        'itihasa' => [
            'name' => ['id' => 'Itihasa', 'en' => 'Itihasa (Hindu epics)'],
            'description' => [
                'id' => 'Tokoh dari epos Hindu: Ramayana, Mahabharata, Adiparwa, Uttarakanda.',
                'en' => 'Figures from the Hindu epics: the Ramayana, the Mahabharata, the Adiparwa, the Uttarakanda.',
            ],
        ],
        'monumen-tokoh-sejarah' => [
            'group' => 'monumen',
            'name' => ['id' => 'Monumen · Tokoh Sejarah', 'en' => 'Monument · Historical figure'],
            'description' => [
                'id' => 'Memperingati momentum sejarah, berwujud figur manusia seperti pahlawan atau tokoh nyata.',
                'en' => 'Commemorates a historical moment in the form of a human figure, such as a national hero.',
            ],
        ],
        'monumen-tugu' => [
            'group' => 'monumen',
            'name' => ['id' => 'Monumen · Tugu', 'en' => 'Monument · Memorial column'],
            'description' => [
                'id' => 'Memperingati momentum sejarah, berwujud tugu, pilar, atau obelisk, bukan figur manusia.',
                'en' => 'Commemorates a historical moment as a column, pillar, or obelisk rather than a human figure.',
            ],
        ],
        'tokoh-babad' => [
            'name' => ['id' => 'Tokoh Babad (Folklor)', 'en' => 'Babad figure (folklore)'],
            'description' => [
                'id' => 'Tokoh dari teks Babad atau cerita rakyat yang dipercaya keberadaannya turun-temurun.',
                'en' => 'A figure from the Balinese Babad chronicles or folk tales, believed to have existed.',
            ],
        ],
        'ikon-daerah' => [
            'name' => ['id' => 'Ikon Daerah', 'en' => 'Regional icon'],
            'description' => [
                'id' => 'Simbol, hewan, hasil bumi, atau aktivitas warga yang menjadi identitas khas suatu daerah.',
                'en' => 'A symbol, animal, crop, or local activity that marks the identity of one area.',
            ],
        ],
        'figur-kanda' => [
            'name' => ['id' => 'Figur / Kanda', 'en' => 'Kanda figure'],
            'description' => [
                'id' => 'Figur dari lontar kelompok Kanda (pengetahuan praktis), kepercayaan lokal yang dikenal di seluruh Bali.',
                'en' => 'A figure from the Kanda group of palm-leaf texts (practical knowledge), a belief shared across Bali.',
            ],
        ],
        'religi' => [
            'name' => ['id' => 'Religi', 'en' => 'Religious devotion'],
            'description' => [
                'id' => 'Figur keagamaan di luar epos Itihasa yang menjadi sarana pemujaan langsung.',
                'en' => 'A religious figure outside the Itihasa epics that is worshipped directly.',
            ],
        ],
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

        $order = 0;
        foreach (self::THEMES as $slug => $theme) {
            Theme::query()->updateOrCreate(['slug' => $slug], [...$theme, 'group' => $theme['group'] ?? null, 'sort_order' => ++$order]);
        }

        $order = 0;
        foreach (self::JUNCTION_TYPES as $slug => $name) {
            JunctionType::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'sort_order' => ++$order]);
        }
    }
}
