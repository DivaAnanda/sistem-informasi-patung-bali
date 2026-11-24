<?php

namespace Tests\Feature\Admin;

use App\Enums\StatueStatus;
use App\Models\Statue;

class AdminStatueTest extends AdminTestCase
{
    public function test_list_shows_every_statue_with_its_status_and_can_be_searched(): void
    {
        $this->statue(['name' => 'Patung Terbit']);
        $this->statue(['name' => 'Patung Draf', 'status' => StatueStatus::Draft]);
        $this->statue(['name' => 'Patung Sembunyi', 'status' => StatueStatus::Hidden]);

        $this->logIn()->get('/admin')
            ->assertOk()
            ->assertSee(['Patung Terbit', 'Patung Draf', 'Patung Sembunyi', 'Draf', 'Disembunyikan']);

        $this->get('/admin?q=draf')->assertSee('Patung Draf')->assertDontSee('Patung Terbit');
        $this->get('/admin?status=hidden')->assertSee('Patung Sembunyi')->assertDontSee('Patung Draf');
    }

    public function test_new_statue_gets_the_next_number_and_shows_publicly_while_waiting_for_themes(): void
    {
        $this->statue(['source_no' => 100]);
        $this->statue(['source_no' => 120, 'status' => StatueStatus::Hidden]);

        $response = $this->logIn()->post('/admin/patung', $this->validForm());

        $statue = Statue::withUnpublished()->where('name', 'Patung Garuda Baru')->firstOrFail();
        $response->assertRedirect('/admin/patung/'.$statue->id);
        $this->assertSame(121, $statue->source_no);
        $this->assertSame('patung-garuda-baru', $statue->slug);
        $this->assertSame(StatueStatus::Published, $statue->status);
        $this->assertSame(['itihasa'], $statue->proposed_themes);
        $this->assertSame([['title' => 'Berita Bali', 'url' => 'https://example.com/garuda']], $statue->sources);
        $this->assertSame([], $statue->photos ?? []);
        $this->assertCount(0, $statue->themes);

        $this->get('/id/patung/patung-garuda-baru')
            ->assertOk()
            ->assertSee('Patung Garuda Baru')
            ->assertSee('Menunggu klasifikasi informan');
    }

    public function test_duplicate_names_get_a_unique_address(): void
    {
        $this->statue(['name' => 'Patung Garuda Baru', 'slug' => 'patung-garuda-baru']);

        $this->logIn()->post('/admin/patung', $this->validForm());

        $this->assertTrue(Statue::withUnpublished()->where('slug', 'patung-garuda-baru-2')->exists());
    }

    public function test_invalid_input_is_rejected_with_readable_messages(): void
    {
        $this->logIn()
            ->from('/admin/patung/baru')
            ->post('/admin/patung', $this->validForm([
                'name' => '',
                'latitude' => '-6.2000000',
                'sources' => [['title' => 'Tautan', 'url' => 'javascript:alert(1)']],
                'status' => 'deleted',
            ]))
            ->assertRedirect('/admin/patung/baru')
            ->assertSessionHasErrors(['name', 'latitude', 'sources.0.url', 'status']);

        $this->assertSame(0, Statue::withUnpublished()->count());
    }

    public function test_editing_updates_fields_and_hiding_takes_the_statue_off_public_pages(): void
    {
        $statue = $this->statue(['name' => 'Patung Lama', 'slug' => 'patung-lama']);

        $this->logIn()
            ->put('/admin/patung/'.$statue->id, $this->validForm(['name' => 'Patung Lama Diperbarui', 'status' => 'hidden']))
            ->assertRedirect('/admin/patung/'.$statue->id);

        $statue = Statue::withUnpublished()->findOrFail($statue->id);
        $this->assertSame('Patung Lama Diperbarui', $statue->name);
        $this->assertSame('patung-lama', $statue->slug, 'The public address must not change when a name is edited.');
        $this->assertSame(StatueStatus::Hidden, $statue->status);
        $this->get('/id/patung/patung-lama')->assertNotFound();

        $this->get('/admin/patung/'.$statue->id)->assertOk()->assertSee('Patung Lama Diperbarui');
    }

    public function test_guests_cannot_write(): void
    {
        $statue = $this->statue();

        $this->post('/admin/patung', $this->validForm())->assertRedirect('/admin/masuk');
        $this->put('/admin/patung/'.$statue->id, $this->validForm())->assertRedirect('/admin/masuk');
        $this->assertSame(1, Statue::withUnpublished()->count());
    }

    public function test_export_contains_every_statue_in_the_import_format(): void
    {
        $this->statue(['name' => 'Patung Terbit', 'source_no' => 1]);
        $this->statue(['name' => 'Patung Sembunyi', 'source_no' => 2, 'status' => StatueStatus::Hidden]);

        $csv = $this->logIn()->get('/admin/ekspor.csv')->assertOk()->streamedContent();

        $this->assertStringStartsWith(implode(',', \App\Services\StatueImport\StatueImporter::COLUMNS).',status', ltrim($csv, "\xEF\xBB\xBF"));
        $this->assertStringContainsString('Patung Sembunyi', $csv);
        $this->assertStringContainsString(',hidden', $csv);
    }
}
