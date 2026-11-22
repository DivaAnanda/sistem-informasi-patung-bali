<?php

namespace Tests\Feature;

use App\Models\JunctionType;
use App\Models\Regency;
use App\Models\Statue;
use App\Models\Theme;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatueBrowsingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_home_shows_counts_and_statues(): void
    {
        $this->statue(['name' => 'Patung Satria Gatotkaca']);

        $this->get('/id')
            ->assertOk()
            ->assertSee('Patung Satria Gatotkaca')
            ->assertSee('home-map-data', false);
    }

    public function test_index_filters_by_regency(): void
    {
        $this->statue(['name' => 'Patung Badung', 'regency_id' => $this->regency('badung')]);
        $this->statue(['name' => 'Patung Gianyar', 'regency_id' => $this->regency('gianyar')]);

        $this->get('/id/patung?kabupaten=gianyar')
            ->assertOk()
            ->assertSee('Patung Gianyar')
            ->assertDontSee('Patung Badung');
    }

    public function test_index_searches_name_district_and_village(): void
    {
        $this->statue(['name' => 'Patung Catur Muka', 'district' => 'Kuta Utara', 'village' => 'Dalung']);
        $this->statue(['name' => 'Patung Kesatria Jatayu', 'district' => 'Petang', 'village' => 'Pangsan']);

        $this->get('/id/patung?q=catur')->assertSee('Patung Catur Muka')->assertDontSee('Patung Kesatria Jatayu');
        $this->get('/id/patung?q=pangsan')->assertSee('Patung Kesatria Jatayu')->assertDontSee('Patung Catur Muka');
    }

    public function test_index_filters_by_junction_type(): void
    {
        $roundabout = JunctionType::query()->where('slug', 'bundaran')->value('id');
        $this->statue(['name' => 'Patung Bundaran', 'junction_type_id' => $roundabout, 'junction_arms' => 5]);
        $this->statue(['name' => 'Patung Tepi Jalan']);

        $this->get('/id/patung?persimpangan=bundaran')
            ->assertSee('Patung Bundaran')
            ->assertSee('Bundaran · 5 arah')
            ->assertDontSee('Patung Tepi Jalan');
    }

    public function test_theme_filter_appears_only_once_statues_are_classified(): void
    {
        $statue = $this->statue(['name' => 'Patung Bertema']);
        $this->statue(['name' => 'Patung Tanpa Tema']);

        $this->get('/id/patung')
            ->assertDontSee('name="tema"', false)
            ->assertSee('sedang diklasifikasi bersama informan');

        $statue->themes()->attach(Theme::query()->where('slug', 'figur-kanda')->value('id'));

        $this->get('/id/patung?tema=figur-kanda')
            ->assertSee('name="tema"', false)
            ->assertSee('Patung Bertema')
            ->assertDontSee('Patung Tanpa Tema');
    }

    public function test_show_lists_every_theme_and_the_junction_guardian(): void
    {
        $crossroads = JunctionType::query()->where('slug', 'perempatan')->value('id');
        $statue = $this->statue(['junction_type_id' => $crossroads, 'junction_arms' => 4]);
        $statue->themes()->attach(Theme::query()->whereIn('slug', ['tokoh-babad', 'monumen-tokoh-sejarah'])->pluck('id'));

        $this->get("/id/patung/{$statue->slug}")
            ->assertSeeInOrder(['Monumen · Tokoh Sejarah', 'Tokoh Babad (Folklor)'])
            ->assertSee('Sang Hyang Catur Bhuwana');
        $this->get("/en/patung/{$statue->slug}")
            ->assertSee('Babad figure (folklore)');
    }

    public function test_no_guardian_is_named_for_a_statue_beside_the_road(): void
    {
        $roadside = JunctionType::query()->where('slug', 'pinggir-jalan')->value('id');
        $statue = $this->statue(['junction_type_id' => $roadside]);

        $this->get("/id/patung/{$statue->slug}")
            ->assertDontSee('Tutur Gong Besi')
            ->assertSee('Menunggu klasifikasi informan');
    }

    public function test_index_ignores_malformed_filters_instead_of_redirecting(): void
    {
        $this->statue(['name' => 'Patung Mahakala']);

        $this->get('/id/patung?q='.str_repeat('a', 500))->assertOk()->assertSee('Tidak ada patung yang cocok.');
        $this->get('/id/patung?kabupaten[]=badung&q=%20%20')->assertOk()->assertSee('Patung Mahakala');
    }

    public function test_out_of_range_page_redirects_to_the_last_page(): void
    {
        Statue::factory()->count(13)->create();

        $this->get('/id/patung?page=9&q=patung')->assertRedirect(url('/id/patung').'?q=patung&page=2');
    }

    public function test_index_paginates_results(): void
    {
        Statue::factory()->count(13)->create();

        $this->get('/id/patung')->assertSee('Halaman 1 dari 2')->assertSee('13 dari 13 patung');
        $this->get('/en/patung?page=2')->assertSee('Page 2 of 2');
    }

    public function test_index_shows_an_empty_state(): void
    {
        $this->get('/id/patung?q=tidakada')->assertSee('Tidak ada patung yang cocok.');
    }

    public function test_show_displays_details_sources_and_nearby_statues(): void
    {
        $statue = $this->statue([
            'name' => 'Patung Dewa Ruci',
            'latitude' => -8.721678,
            'longitude' => 115.1827293,
            'sources' => [['title' => 'Tribun Bali', 'url' => 'https://bali.tribunnews.com/a'], ['title' => 'Arsip tim', 'url' => null]],
        ]);
        $this->statue(['name' => 'Patung Gatotkaca', 'latitude' => -8.7443801, 'longitude' => 115.1787896]);

        $this->get("/id/patung/{$statue->slug}")
            ->assertOk()
            ->assertSee('Patung Dewa Ruci')
            ->assertSee('href="https://bali.tribunnews.com/a"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('Arsip tim')
            ->assertSee('Patung terdekat')
            ->assertSee('Patung Gatotkaca')
            ->assertSee('2,6 km dari sini')
            ->assertSee('Menunggu klasifikasi informan')
            ->assertSee('destination=-8.721678,115.1827293', false);
    }

    public function test_show_returns_404_for_an_unknown_statue(): void
    {
        $this->get('/id/patung/tidak-ada')->assertNotFound();
    }

    public function test_english_page_falls_back_to_the_indonesian_description(): void
    {
        $statue = $this->statue(['description' => ['id' => 'Deskripsi berbahasa Indonesia.']]);

        $this->get("/en/patung/{$statue->slug}")
            ->assertSee('Deskripsi berbahasa Indonesia.')
            ->assertSee('only available in Indonesian');
    }

    public function test_english_page_uses_the_english_description_when_present(): void
    {
        $statue = $this->statue(['description' => ['id' => 'Deskripsi.', 'en' => 'An English description.']]);

        $this->get("/en/patung/{$statue->slug}")
            ->assertSee('An English description.')
            ->assertDontSee('only available in Indonesian');
    }

    public function test_map_page_embeds_every_statue_as_a_marker(): void
    {
        $statue = $this->statue(['name' => 'Patung Mahakala']);

        $this->get('/en/peta')
            ->assertOk()
            ->assertSee('full-map-data', false)
            ->assertSee('"name":"Patung Mahakala"', false)
            ->assertSee(json_encode(url("/en/patung/{$statue->slug}")), false);
    }

    public function test_about_page_is_available_in_both_languages(): void
    {
        $this->get('/id/tentang')->assertOk()->assertSee('Tentang proyek')->assertSee('Figur / Kanda')->assertSee('Dewa Gede Purwita');
        $this->get('/en/tentang')->assertOk()->assertSee('About the project')->assertSee('Itihasa (Hindu epics)');
    }

    public function test_distance_between_statues_uses_great_circle_distance(): void
    {
        $origin = new Statue(['latitude' => 0, 'longitude' => 0]);
        $oneDegreeEast = new Statue(['latitude' => 0, 'longitude' => 1]);

        $this->assertEqualsWithDelta(111.19, $origin->distanceTo($oneDegreeEast), 0.01);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function statue(array $attributes = []): Statue
    {
        return Statue::factory()->create(['regency_id' => $this->regency('badung'), ...$attributes]);
    }

    private function regency(string $slug): int
    {
        return Regency::query()->where('slug', $slug)->value('id');
    }
}
