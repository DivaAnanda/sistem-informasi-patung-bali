<?php

namespace Tests\Feature;

use App\Models\Statue;
use App\Models\StatueReview;
use App\Support\Expert;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class ExpertReviewTest extends TestCase
{
    use RefreshDatabase;

    private const EXPERT_TOKEN = 'token-pakar-uji-panjang';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_secret_link_turns_on_informant_mode(): void
    {
        $this->get('/id/validasi/masuk/token-pakar-uji-panjang')
            ->assertRedirect(url('/id/validasi'))
            ->assertSessionHas(Expert::SESSION_KEY, Expert::fingerprint(self::EXPERT_TOKEN));

        $this->get('/id/validasi')->assertOk()->assertSee('Klasifikasi tema patung');
    }

    public function test_wrong_link_and_guests_get_not_found(): void
    {
        $statue = Statue::factory()->create();

        $this->get('/id/validasi/masuk/salah')->assertNotFound();
        $this->get('/id/validasi')->assertNotFound();
        $this->putJson("/id/validasi/{$statue->slug}", ['themes' => ['itihasa']])->assertNotFound();
        $this->assertSame(0, StatueReview::query()->count());
    }

    public function test_public_page_hides_the_panel_and_the_team_proposal(): void
    {
        $statue = $this->statueProposedAs(['tokoh-babad', 'monumen-tokoh-sejarah']);

        $this->get("/id/patung/{$statue->slug}")
            ->assertOk()
            ->assertDontSee('data-review-panel', false)
            ->assertDontSee('Usulan tim')
            ->assertDontSee('Dasar usulan dari rekaman');
    }

    public function test_informant_sees_the_panel_with_the_team_proposal_in_catalog_order(): void
    {
        $statue = $this->statueProposedAs(['tokoh-babad', 'monumen-tokoh-sejarah']);

        $this->asExpert()->get("/id/patung/{$statue->slug}")
            ->assertOk()
            ->assertSee('data-review-panel', false)
            ->assertSee('Monumen · Tokoh Sejarah + Tokoh Babad (Folklor)')
            ->assertSee('Dasar usulan dari rekaman')
            ->assertSee('data-review-theme="religi"', false)
            ->assertSee('name="csrf-token"', false)
            ->assertSee('noindex', false);
    }

    public function test_chosen_themes_are_saved_in_catalog_order_and_reported(): void
    {
        $statue = Statue::factory()->create();
        Statue::factory()->create();

        $this->asExpert()
            ->putJson("/id/validasi/{$statue->slug}", ['themes' => ['tokoh-babad', 'monumen-tokoh-sejarah', 'tokoh-babad'], 'notes' => ' Raja dalam babad. '])
            ->assertOk()
            ->assertJson(['reviewed' => true, 'progress' => ['done' => 1, 'total' => 2]]);

        $review = StatueReview::query()->sole();
        $this->assertSame($statue->source_no, $review->source_no);
        $this->assertSame(['monumen-tokoh-sejarah', 'tokoh-babad'], $review->themes);
        $this->assertSame('Raja dalam babad.', $review->notes);
        $this->assertNotNull($review->reviewed_at);
    }

    public function test_notes_alone_and_cleared_themes_do_not_count_as_classified(): void
    {
        $statue = Statue::factory()->create();

        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", ['themes' => ['religi']]);
        $this->asExpert()
            ->putJson("/id/validasi/{$statue->slug}", ['themes' => [], 'notes' => 'Perlu foto lain'])
            ->assertJson(['reviewed' => false, 'progress' => ['done' => 0]]);

        $review = StatueReview::query()->sole();
        $this->assertSame([], $review->themes);
        $this->assertNull($review->reviewed_at);
        $this->assertSame('Perlu foto lain', $review->notes);
    }

    public function test_invalid_choices_are_rejected(): void
    {
        $statue = Statue::factory()->create();

        $this->asExpert()
            ->putJson("/id/validasi/{$statue->slug}", ['themes' => ['budaya']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('themes.0');
        $this->asExpert()
            ->putJson("/id/validasi/{$statue->slug}", ['themes' => 'itihasa', 'notes' => str_repeat('a', 5001)])
            ->assertJsonValidationErrors(['themes', 'notes']);
        $this->assertSame(0, StatueReview::query()->count());
    }

    public function test_next_unclassified_link_skips_classified_statues_and_wraps_around(): void
    {
        [$first, $second, $third] = collect([1, 2, 3])
            ->map(fn (int $no) => Statue::factory()->create(['source_no' => $no, 'name' => "Patung Nomor {$no}", 'slug' => "patung-nomor-{$no}"]))
            ->all();
        StatueReview::query()->create(['source_no' => 3, 'themes' => ['itihasa']]);

        $this->asExpert()->get("/id/patung/{$second->slug}")
            ->assertSee('data-review-panel', false)
            ->assertSee('href="'.url("/id/patung/{$first->slug}").'"', false)
            ->assertSeeInOrder(['Lanjut ke yang belum diklasifikasi', 'Sebelumnya']);

        $this->assertStringContainsString(
            url("/id/patung/{$first->slug}"),
            $this->nextUnclassifiedHref($this->asExpert()->get("/id/patung/{$second->slug}")->getContent())
        );
        $this->assertStringContainsString(
            url("/id/patung/{$second->slug}"),
            $this->nextUnclassifiedHref($this->asExpert()->get("/id/patung/{$first->slug}")->getContent())
        );
        $this->assertNotNull($third);
    }

    public function test_review_list_filters_by_status_and_shows_chosen_themes(): void
    {
        Statue::factory()->create(['source_no' => 1, 'name' => 'Patung Sudah Dicek']);
        Statue::factory()->create(['source_no' => 2, 'name' => 'Patung Belum Dicek']);
        StatueReview::query()->create(['source_no' => 1, 'themes' => ['figur-kanda']]);

        $this->asExpert()->get('/id/validasi?status=belum')
            ->assertSee('Patung Belum Dicek')
            ->assertDontSee('Patung Sudah Dicek');
        $this->asExpert()->get('/id/validasi?status=sudah&q=sudah')
            ->assertSee('Patung Sudah Dicek')
            ->assertSee('Figur / Kanda')
            ->assertDontSee('Patung Belum Dicek');
    }

    public function test_team_can_export_the_classification_as_a_spreadsheet(): void
    {
        $statue = $this->statueProposedAs(['itihasa'], name: 'Patung Ekspor');
        StatueReview::query()->create(['source_no' => $statue->source_no, 'themes' => ['itihasa'], 'notes' => 'Setuju']);
        $other = $this->statueProposedAs(['ikon-daerah'], name: 'Patung Beda');
        StatueReview::query()->create(['source_no' => $other->source_no, 'themes' => ['figur-kanda']]);

        $response = $this->get('/ekspor-validasi/token-ekspor-uji-panjang')->assertOk();
        $sheet = $this->sheetXml($response->streamedContent());

        $this->assertStringContainsString('Patung Ekspor', $sheet);
        $this->assertStringContainsString('Setuju', $sheet);
        $this->assertStringContainsString('Sudah diklasifikasi', $sheet);
        $this->assertStringContainsString('Dasar usulan dari rekaman', $sheet);
        $this->assertStringContainsString('Figur / Kanda', $sheet);
        $this->assertStringContainsString('>Tidak<', $sheet);
        $this->assertStringContainsString('>Ya<', $sheet);
        $this->get('/ekspor-validasi/salah')->assertNotFound();
    }

    public function test_saving_notes_does_not_touch_the_themes(): void
    {
        $statue = Statue::factory()->create();

        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", ['themes' => ['religi']])->assertOk();
        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", ['notes' => 'Catatan dari tab lain'])->assertJson(['reviewed' => true]);
        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", ['themes' => ['itihasa', 'religi']])->assertOk();

        $review = StatueReview::query()->sole();
        $this->assertSame(['itihasa', 'religi'], $review->themes);
        $this->assertSame('Catatan dari tab lain', $review->notes);
        $this->assertSame($statue->name, $review->statue_name);
    }

    public function test_an_empty_save_is_rejected(): void
    {
        $statue = Statue::factory()->create();

        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", [])->assertUnprocessable();
        $this->assertSame(0, StatueReview::query()->count());
    }

    public function test_rotating_the_token_ends_existing_informant_sessions(): void
    {
        $this->asExpert()->get('/id/validasi')->assertOk();

        config(['patung.expert_token' => 'token-pakar-baru-yang-panjang']);

        $this->asExpert()->get('/id/validasi')->assertNotFound();
    }

    public function test_short_tokens_are_never_accepted(): void
    {
        config(['patung.expert_token' => 'pendek']);

        $this->get('/id/validasi/masuk/pendek')->assertNotFound();
    }

    public function test_export_warns_when_a_number_now_belongs_to_another_statue(): void
    {
        $statue = Statue::factory()->create(['name' => 'Patung Pengganti']);
        StatueReview::query()->create(['source_no' => $statue->source_no, 'statue_name' => 'Patung Lama', 'themes' => ['itihasa']]);

        $sheet = $this->sheetXml($this->get('/ekspor-validasi/token-ekspor-uji-panjang')->streamedContent());

        $this->assertStringContainsString('Diklasifikasi saat bernama', $sheet);
        $this->assertStringContainsString('Patung Lama', $sheet);
    }

    public function test_a_notes_only_save_keeps_the_name_the_themes_were_chosen_for(): void
    {
        $statue = Statue::factory()->create(['name' => 'Patung Pengganti']);
        StatueReview::query()->create(['source_no' => $statue->source_no, 'statue_name' => 'Patung Lama', 'themes' => ['itihasa']]);

        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", ['notes' => 'Catatan baru'])->assertOk();
        $this->assertSame('Patung Lama', StatueReview::query()->sole()->statue_name);

        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", ['themes' => ['religi']])->assertOk();
        $this->assertSame('Patung Pengganti', StatueReview::query()->sole()->statue_name);
    }

    public function test_logging_out_ends_informant_mode(): void
    {
        $this->asExpert()->post('/id/validasi/keluar')->assertRedirect(url('/id'))->assertSessionMissing(Expert::SESSION_KEY);
    }

    private function asExpert(): static
    {
        return $this->withSession([Expert::SESSION_KEY => Expert::fingerprint(self::EXPERT_TOKEN)]);
    }

    /**
     * @param  list<string>  $themes
     */
    private function statueProposedAs(array $themes, string $name = 'Patung Usulan'): Statue
    {
        return Statue::factory()->create([
            'name' => $name,
            'proposed_themes' => $themes,
            'proposal_note' => 'Dasar usulan dari rekaman',
        ]);
    }

    private function nextUnclassifiedHref(string $html): string
    {
        preg_match('/href="([^"]+)"[^>]*>\s*Lanjut ke yang belum diklasifikasi/', $html, $match);

        return $match[1] ?? '';
    }

    private function sheetXml(string $xlsx): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $xlsx);
        $zip = new ZipArchive;
        $zip->open($path);
        $xml = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        unlink($path);

        return $xml;
    }
}
