<?php

namespace Tests\Feature;

use App\Enums\StatueStatus;
use App\Models\Regency;
use App\Models\Statue;
use App\Models\Theme;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatueStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_new_statues_are_published_by_default(): void
    {
        $statue = $this->statue(['name' => 'Patung Terbit']);

        $this->assertSame(StatueStatus::Published, $statue->fresh()->status);
    }

    public function test_drafts_and_hidden_statues_stay_off_public_pages(): void
    {
        $this->statue(['name' => 'Patung Terbit']);
        $this->statue(['name' => 'Patung Draf', 'status' => StatueStatus::Draft]);
        $this->statue(['name' => 'Patung Tersembunyi', 'status' => StatueStatus::Hidden]);

        foreach (['/id', '/id/patung', '/id/peta'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Patung Terbit')
                ->assertDontSee('Patung Draf')
                ->assertDontSee('Patung Tersembunyi');
        }
    }

    public function test_unpublished_statue_page_is_not_found(): void
    {
        $draft = $this->statue(['name' => 'Patung Draf', 'status' => StatueStatus::Draft]);

        $this->get('/id/patung/'.$draft->slug)->assertNotFound();
    }

    public function test_counts_ignore_unpublished_statues(): void
    {
        $badung = Regency::query()->where('slug', 'badung')->value('id');
        $itihasa = Theme::query()->where('slug', 'itihasa')->firstOrFail();
        $this->statue(['regency_id' => $badung])->themes()->attach($itihasa);
        $this->statue(['regency_id' => $badung, 'status' => StatueStatus::Hidden])->themes()->attach($itihasa);

        $this->assertSame(1, Regency::query()->withCount('statues')->find($badung)->statues_count);
        $this->assertSame(1, Theme::query()->withCount('statues')->find($itihasa->id)->statues_count);
    }

    public function test_admin_queries_can_still_reach_every_statue(): void
    {
        $this->statue();
        $this->statue(['status' => StatueStatus::Draft]);
        $this->statue(['status' => StatueStatus::Hidden]);

        $this->assertSame(1, Statue::query()->count());
        $this->assertSame(3, Statue::withUnpublished()->count());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function statue(array $attributes = []): Statue
    {
        return Statue::factory()->create(array_merge(
            ['regency_id' => Regency::query()->value('id')],
            $attributes,
        ));
    }
}
