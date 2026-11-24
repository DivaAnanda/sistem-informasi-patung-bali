<?php

namespace Tests\Feature\Admin;

use App\Models\Regency;
use App\Models\Statue;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

abstract class AdminTestCase extends TestCase
{
    use RefreshDatabase;

    protected const EMAIL = 'admin@patungbali.test';

    protected const PASSWORD = 'kata-sandi-uji-yang-panjang';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
        config([
            'patung.admin.email' => self::EMAIL,
            'patung.admin.password_hash' => Hash::make(self::PASSWORD),
            'patung.photo_storage.endpoint' => 'https://example.storage.supabase.co/storage/v1/s3',
            'patung.photo_storage.key' => 'test-key-id',
            'patung.photo_storage.secret' => 'test-secret',
            'patung.photo_storage.public_url' => 'https://example.supabase.co/storage/v1/object/public/patung',
        ]);
    }

    protected function logIn(): static
    {
        $this->post('/admin/masuk', ['email' => self::EMAIL, 'password' => self::PASSWORD])->assertRedirect('/admin');

        return $this;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function statue(array $attributes = []): Statue
    {
        return Statue::factory()->create(array_merge(['regency_id' => $this->regency('badung')], $attributes));
    }

    protected function regency(string $slug): int
    {
        return Regency::query()->where('slug', $slug)->value('id');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validForm(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Patung Garuda Baru',
            'regency_id' => $this->regency('gianyar'),
            'district' => 'Ubud',
            'village' => 'Peliatan',
            'latitude' => '-8.5130000',
            'longitude' => '115.2630000',
            'junction_type_id' => '',
            'junction_arms' => '',
            'description_id' => 'Patung Garuda di simpang Peliatan.',
            'description_en' => '',
            'sources' => [['title' => 'Berita Bali', 'url' => 'https://example.com/garuda']],
            'proposed_themes' => ['itihasa'],
            'proposal_note' => 'Dugaan tim: tokoh epos.',
            'status' => 'published',
        ], $overrides);
    }
}
