<?php

namespace Tests\Feature;

use App\Models\Regency;
use App\Models\Statue;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PhotosAndStreetViewTest extends TestCase
{
    use RefreshDatabase;

    private string $publicPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
        $this->publicPath = sys_get_temp_dir().'/patung-public-'.uniqid();
        File::ensureDirectoryExists($this->publicPath.'/images/patung');
        $this->app->usePublicPath($this->publicPath);
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publicPath);

        parent::tearDown();
    }

    public function test_thumbnail_command_creates_small_copies_and_skips_fresh_ones(): void
    {
        $this->jpeg('001-1.jpg', 1280, 960);

        $this->artisan('patung:thumbnails')->expectsOutputToContain('1 thumbnail dibuat')->assertSuccessful();
        $this->artisan('patung:thumbnails')->expectsOutputToContain('0 thumbnail dibuat')->assertSuccessful();

        [$width, $height] = getimagesize($this->publicPath.'/images/patung/thumbs/001-1.jpg');
        $this->assertSame(640, $width);
        $this->assertSame(480, $height);
    }

    public function test_cards_use_the_thumbnail_and_the_detail_page_offers_the_full_photo(): void
    {
        $this->jpeg('001-1.jpg', 800, 600);
        $this->jpeg('001-2.jpg', 600, 800);
        $this->artisan('patung:thumbnails');
        $statue = $this->statue(['photos' => ['001-1.jpg', '001-2.jpg']]);

        $this->get('/id/patung')->assertSee('images/patung/thumbs/001-1.jpg', false);
        $this->get("/id/patung/{$statue->slug}")
            ->assertSee('data-gallery-dialog', false)
            ->assertSee('Lihat 2 foto')
            ->assertSee('images\/patung\/001-2.jpg', false);
    }

    public function test_street_view_links_out_when_no_embed_key_is_configured(): void
    {
        config(['services.google_maps.embed_key' => null]);
        $statue = $this->statue(['latitude' => -8.65, 'longitude' => 115.21]);

        $this->get("/id/patung/{$statue->slug}")
            ->assertSee('map_action=pano&amp;viewpoint=-8.65,115.21', false)
            ->assertDontSee('data-street-view-load', false);
    }

    public function test_street_view_embeds_the_panorama_when_a_key_is_configured(): void
    {
        config(['services.google_maps.embed_key' => 'kunci-uji']);
        $statue = $this->statue(['latitude' => -8.65, 'longitude' => 115.21]);

        $this->get("/id/patung/{$statue->slug}")
            ->assertSee('data-street-view-load="https://www.google.com/maps/embed/v1/streetview?key=kunci-uji&amp;location=-8.65%2C115.21', false);
    }

    public function test_home_links_each_regency_to_its_filtered_list(): void
    {
        $this->statue();

        $this->get('/id')
            ->assertOk()
            ->assertSee('Jelajahi per kabupaten')
            ->assertSee('href="'.url('/id/patung').'?kabupaten=badung"', false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function statue(array $attributes = []): Statue
    {
        return Statue::factory()->create([
            'regency_id' => Regency::query()->where('slug', 'badung')->value('id'),
            ...$attributes,
        ]);
    }

    private function jpeg(string $name, int $width, int $height): void
    {
        $image = imagecreatetruecolor($width, $height);
        imagejpeg($image, $this->publicPath.'/images/patung/'.$name);
    }
}
