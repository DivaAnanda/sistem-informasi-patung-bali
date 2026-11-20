<?php

namespace Tests\Feature;

use App\Models\Statue;
use App\Models\StatueReview;
use App\Models\StatueType;
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

    public function test_secret_link_turns_on_expert_mode(): void
    {
        $this->get('/id/validasi/masuk/token-pakar-uji-panjang')
            ->assertRedirect(url('/id/validasi'))
            ->assertSessionHas(Expert::SESSION_KEY, Expert::fingerprint(self::EXPERT_TOKEN));

        $this->get('/id/validasi')->assertOk()->assertSee('Validasi jenis patung');
    }

    public function test_wrong_link_and_guests_get_not_found(): void
    {
        $statue = Statue::factory()->create();

        $this->get('/id/validasi/masuk/salah')->assertNotFound();
        $this->get('/id/validasi')->assertNotFound();
        $this->putJson("/id/validasi/{$statue->slug}", ['is_iconic' => true])->assertNotFound();
        $this->assertSame(0, StatueReview::query()->count());
    }

    public function test_public_page_hides_the_panel_and_the_team_proposal(): void
    {
        $statue = $this->statueProposedAs('budaya', iconic: true);

        $this->get("/id/patung/{$statue->slug}")
            ->assertOk()
            ->assertDontSee('data-review-panel', false)
            ->assertDontSee('Usulan tim');
    }

    public function test_expert_sees_the_panel_with_the_team_proposal(): void
    {
        $statue = $this->statueProposedAs('budaya', iconic: true);

        $this->asExpert()->get("/id/patung/{$statue->slug}")
            ->assertOk()
            ->assertSee('data-review-panel', false)
            ->assertSee('Budaya + Ikonik')
            ->assertSee('name="csrf-token"', false)
            ->assertSee('noindex', false);
    }

    public function test_expert_choice_is_saved_and_reported(): void
    {
        $statue = Statue::factory()->create();
        Statue::factory()->create();

        $this->asExpert()
            ->putJson("/id/validasi/{$statue->slug}", ['statue_type' => 'religius', 'is_iconic' => true, 'notes' => ' Sudah dipasupati. '])
            ->assertOk()
            ->assertJson(['reviewed' => true, 'progress' => ['done' => 1, 'total' => 2]]);

        $review = StatueReview::query()->sole();
        $this->assertSame($statue->source_no, $review->source_no);
        $this->assertSame('religius', $review->statue_type);
        $this->assertTrue($review->is_iconic);
        $this->assertSame('Sudah dipasupati.', $review->notes);
        $this->assertNotNull($review->reviewed_at);
    }

    public function test_notes_alone_and_cleared_choices_do_not_count_as_reviewed(): void
    {
        $statue = Statue::factory()->create();

        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", ['statue_type' => 'budaya', 'is_iconic' => false]);
        $this->asExpert()
            ->putJson("/id/validasi/{$statue->slug}", ['statue_type' => null, 'is_iconic' => false, 'notes' => 'Perlu foto lain'])
            ->assertJson(['reviewed' => false, 'progress' => ['done' => 0]]);

        $review = StatueReview::query()->sole();
        $this->assertNull($review->reviewed_at);
        $this->assertSame('Perlu foto lain', $review->notes);
    }

    public function test_invalid_choices_are_rejected(): void
    {
        $statue = Statue::factory()->create();

        $this->asExpert()
            ->putJson("/id/validasi/{$statue->slug}", ['statue_type' => 'ikonik', 'is_iconic' => false])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('statue_type');
        $this->asExpert()
            ->putJson("/id/validasi/{$statue->slug}", ['statue_type' => 'budaya', 'notes' => str_repeat('a', 5001)])
            ->assertJsonValidationErrors(['is_iconic', 'notes']);
    }

    public function test_next_unreviewed_link_skips_reviewed_statues_and_wraps_around(): void
    {
        [$first, $second, $third] = collect([1, 2, 3])
            ->map(fn (int $no) => Statue::factory()->create(['source_no' => $no, 'name' => "Patung Nomor {$no}", 'slug' => "patung-nomor-{$no}"]))
            ->all();
        StatueReview::query()->create(['source_no' => 3, 'statue_type' => 'budaya', 'is_iconic' => false]);

        $this->asExpert()->get("/id/patung/{$second->slug}")
            ->assertSee('data-review-panel', false)
            ->assertSee('href="'.url("/id/patung/{$first->slug}").'"', false)
            ->assertSeeInOrder(['Lanjut ke yang belum diperiksa', 'Sebelumnya']);

        $this->assertStringContainsString(
            url("/id/patung/{$first->slug}"),
            $this->nextUnreviewedHref($this->asExpert()->get("/id/patung/{$second->slug}")->getContent())
        );
        $this->assertStringContainsString(
            url("/id/patung/{$second->slug}"),
            $this->nextUnreviewedHref($this->asExpert()->get("/id/patung/{$first->slug}")->getContent())
        );
        $this->assertNotNull($third);
    }

    public function test_review_list_filters_by_status(): void
    {
        Statue::factory()->create(['source_no' => 1, 'name' => 'Patung Sudah Dicek']);
        Statue::factory()->create(['source_no' => 2, 'name' => 'Patung Belum Dicek']);
        StatueReview::query()->create(['source_no' => 1, 'statue_type' => 'dekoratif', 'is_iconic' => false]);

        $this->asExpert()->get('/id/validasi?status=belum')
            ->assertSee('Patung Belum Dicek')
            ->assertDontSee('Patung Sudah Dicek');
        $this->asExpert()->get('/id/validasi?status=sudah&q=sudah')
            ->assertSee('Patung Sudah Dicek')
            ->assertDontSee('Patung Belum Dicek');
    }

    public function test_team_can_export_the_reviews_as_a_spreadsheet(): void
    {
        $statue = $this->statueProposedAs('budaya', iconic: false, name: 'Patung Ekspor');
        StatueReview::query()->create(['source_no' => $statue->source_no, 'statue_type' => 'budaya', 'is_iconic' => false, 'notes' => 'Setuju']);

        $response = $this->get('/ekspor-validasi/token-ekspor-uji-panjang')->assertOk();
        $strings = $this->sheetXml($response->streamedContent());

        $this->assertStringContainsString('Patung Ekspor', $strings);
        $this->assertStringContainsString('Setuju', $strings);
        $this->assertStringContainsString('Sudah diperiksa', $strings);
        $this->get('/ekspor-validasi/salah')->assertNotFound();
    }

    public function test_saving_notes_does_not_touch_the_classification(): void
    {
        $statue = Statue::factory()->create();

        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", ['statue_type' => 'religius', 'is_iconic' => true])->assertOk();
        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", ['notes' => 'Catatan dari tab lain'])->assertJson(['reviewed' => true]);
        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", ['statue_type' => 'budaya', 'is_iconic' => false])->assertOk();

        $review = StatueReview::query()->sole();
        $this->assertSame('budaya', $review->statue_type);
        $this->assertFalse($review->is_iconic);
        $this->assertSame('Catatan dari tab lain', $review->notes);
        $this->assertSame($statue->name, $review->statue_name);
    }

    public function test_an_empty_save_is_rejected(): void
    {
        $statue = Statue::factory()->create();

        $this->asExpert()->putJson("/id/validasi/{$statue->slug}", [])->assertUnprocessable();
        $this->assertSame(0, StatueReview::query()->count());
    }

    public function test_rotating_the_token_ends_existing_expert_sessions(): void
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
        StatueReview::query()->create(['source_no' => $statue->source_no, 'statue_name' => 'Patung Lama', 'statue_type' => 'budaya', 'is_iconic' => false]);

        $sheet = $this->sheetXml($this->get('/ekspor-validasi/token-ekspor-uji-panjang')->streamedContent());

        $this->assertStringContainsString('Dinilai saat bernama', $sheet);
        $this->assertStringContainsString('Patung Lama', $sheet);
    }

    public function test_logging_out_ends_expert_mode(): void
    {
        $this->asExpert()->post('/id/validasi/keluar')->assertRedirect(url('/id'))->assertSessionMissing(Expert::SESSION_KEY);
    }

    private function asExpert(): static
    {
        return $this->withSession([Expert::SESSION_KEY => Expert::fingerprint(self::EXPERT_TOKEN)]);
    }

    private function statueProposedAs(string $type, bool $iconic, string $name = 'Patung Usulan'): Statue
    {
        return Statue::factory()->create([
            'name' => $name,
            'proposed_type_id' => StatueType::query()->where('slug', $type)->value('id'),
            'proposed_iconic' => $iconic,
        ]);
    }

    private function nextUnreviewedHref(string $html): string
    {
        preg_match('/href="([^"]+)"[^>]*>\s*Lanjut ke yang belum diperiksa/', $html, $match);

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
