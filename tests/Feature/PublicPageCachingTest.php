<?php

namespace Tests\Feature;

use App\Models\Statue;
use App\Support\Expert;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPageCachingTest extends TestCase
{
    use RefreshDatabase;

    private const EXPERT_TOKEN = 'token-pakar-uji-panjang';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_anonymous_public_pages_are_cacheable_and_set_no_cookies(): void
    {
        $statue = Statue::factory()->create();

        foreach (['/id', '/id/patung', "/id/patung/{$statue->slug}", '/id/peta', '/id/dokumentasi', '/en/tentang'] as $path) {
            $response = $this->get($path)->assertOk();

            $this->assertStringContainsString('s-maxage=600', $response->headers->get('Cache-Control'), $path);
            $this->assertStringContainsString('public', $response->headers->get('Cache-Control'), $path);
            $this->assertSame([], $response->headers->getCookies(), $path);
        }
    }

    public function test_informant_pages_are_never_cached(): void
    {
        $statue = Statue::factory()->create();
        $session = [Expert::SESSION_KEY => Expert::fingerprint(self::EXPERT_TOKEN)];

        $response = $this->withSession($session)->get("/id/patung/{$statue->slug}")->assertOk();
        $this->assertStringNotContainsString('s-maxage', $response->headers->get('Cache-Control'));

        $this->withSession($session)->get('/id/validasi')
            ->assertOk()
            ->assertSee("/id/patung/{$statue->slug}?validasi=1", false);
    }

    public function test_validation_links_bypass_the_cache_even_for_strangers(): void
    {
        $statue = Statue::factory()->create();

        $response = $this->get("/id/patung/{$statue->slug}?validasi=1")->assertOk();

        $this->assertStringNotContainsString('s-maxage', $response->headers->get('Cache-Control'));
    }

    public function test_errors_and_admin_pages_are_not_cached(): void
    {
        $missing = $this->get('/id/patung/tidak-ada')->assertNotFound();
        $this->assertStringNotContainsString('s-maxage', (string) $missing->headers->get('Cache-Control'));

        $login = $this->get('/admin/masuk');
        $this->assertStringNotContainsString('s-maxage', (string) $login->headers->get('Cache-Control'));
    }
}
