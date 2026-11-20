<?php

namespace Tests\Feature;

use App\Models\Statue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_indonesian_speaking_browsers_to_indonesian(): void
    {
        $this->withHeader('Accept-Language', 'id-ID,id;q=0.9')->get('/')->assertRedirect('/id');
    }

    public function test_root_falls_back_to_indonesian_for_other_languages(): void
    {
        $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')->get('/')->assertRedirect('/id');
    }

    public function test_root_redirects_english_speaking_browsers_to_english(): void
    {
        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/')->assertRedirect('/en');
    }

    public function test_unsupported_locale_returns_404(): void
    {
        $this->get('/fr')->assertNotFound();
    }

    public function test_pages_render_in_the_requested_language(): void
    {
        $this->get('/id')->assertOk()->assertSee('<html lang="id">', false)->assertSee('Jelajahi patung');
        $this->get('/en')->assertOk()->assertSee('<html lang="en">', false)->assertSee('Explore statues');
    }

    public function test_language_switcher_keeps_the_current_page_and_filters(): void
    {
        $this->get('/id/patung?kabupaten=badung')
            ->assertSee('href="'.url('/en/patung').'?kabupaten=badung"', false);
    }

    public function test_language_switcher_on_a_static_page_has_a_clean_url(): void
    {
        $this->get('/id/tentang')
            ->assertSee('hreflang="en" href="'.url('/en/tentang').'"', false)
            ->assertDontSee('view=pages.about', false);
    }

    public function test_language_switcher_keeps_the_current_statue(): void
    {
        $statue = Statue::factory()->create();

        $this->get("/id/patung/{$statue->slug}")
            ->assertSee('hreflang="en" href="'.url("/en/patung/{$statue->slug}").'"', false);
    }
}
