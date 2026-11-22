<?php

namespace Tests\Feature;

use App\Models\Statue;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_documentation_page_shows_the_visit_and_the_interview_in_both_languages(): void
    {
        $this->get('/id/dokumentasi')
            ->assertOk()
            ->assertSee('Kunjungan ke Monumen Puputan Badung')
            ->assertSee('Wawancara dengan informan budaya')
            ->assertSee('Dewa Gede Purwita, S.Pd., M.Sn.')
            ->assertSee('datetime="2026-11-20"', false)
            ->assertSee('demo-website-informan.mp4');

        $this->get('/en/dokumentasi')
            ->assertOk()
            ->assertSee('Visit to the Puputan Badung Monument')
            ->assertSee('Interview with a cultural informant');
    }

    public function test_documentation_links_to_the_monument_page_when_it_is_in_the_dataset(): void
    {
        $this->get('/id/dokumentasi')->assertDontSee('Lihat halaman patungnya');

        $monument = Statue::factory()->create(['source_no' => 83, 'name' => 'Monumen Puputan Badung']);

        $this->get('/id/dokumentasi')
            ->assertSee('Lihat halaman patungnya')
            ->assertSee('href="'.url("/id/patung/{$monument->slug}").'"', false);
    }

    public function test_every_documentation_photo_and_thumbnail_exists(): void
    {
        $html = $this->get('/id/dokumentasi')->getContent();
        preg_match_all('#images/dokumentasi/([\w/.-]+\.(?:jpg|mp4))#', $html, $matches);
        $files = array_unique($matches[1]);

        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertFileExists(public_path("images/dokumentasi/{$file}"));
        }
        foreach (array_filter($files, fn (string $file) => str_starts_with($file, 'thumbs/')) as $thumb) {
            $this->assertFileExists(public_path('images/dokumentasi/'.substr($thumb, strlen('thumbs/'))));
        }
    }

    public function test_home_opens_with_the_parallax_hero_above_the_introduction(): void
    {
        $html = $this->get('/id')
            ->assertOk()
            ->assertSee('data-parallax', false)
            ->assertSeeInOrder(['id="hero-title"', 'Patung', 'Bali', 'Patung-patung yang menjaga persimpangan Bali'], false)
            ->getContent();

        preg_match_all('#images/hero/([\w.-]+\.webp)#', $html, $matches);
        $this->assertCount(6, array_unique($matches[1]));
        foreach (array_unique($matches[1]) as $file) {
            $this->assertFileExists(public_path("images/hero/{$file}"));
        }
    }

    public function test_main_navigation_links_to_the_documentation(): void
    {
        $this->get('/en/tentang')->assertSee('>Documentation</a>', false);
        $this->get('/id/dokumentasi')->assertSee('aria-current="page"', false);
    }
}
