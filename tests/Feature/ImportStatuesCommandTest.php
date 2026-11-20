<?php

namespace Tests\Feature;

use App\Models\Statue;
use App\Services\StatueImport\StatueImporter;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportStatuesCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    protected function tearDown(): void
    {
        array_map('unlink', array_filter($this->tempFiles, 'is_file'));

        parent::tearDown();
    }

    public function test_imports_rows_with_parsed_junction_and_sources(): void
    {
        $path = $this->csv([
            $this->row([
                'no' => '6',
                'junction_type' => 'Bundaran (5 arah)',
                'sources' => 'NusaBali - Berita [https://www.nusabali.com/berita/1]; Catatan lapangan tim',
                'description_en' => 'Bima fighting the serpent.',
            ]),
            $this->row(['no' => '7', 'name' => 'Patung Catur Muka', 'regency' => 'Kabupaten Badung', 'junction_type' => 'Perlu verifikasi']),
        ]);

        $this->artisan('patung:import', ['file' => $path])->assertSuccessful();

        $statue = Statue::query()->with('junctionType')->firstWhere('source_no', 6);
        $this->assertSame('bundaran', $statue->junctionType->slug);
        $this->assertSame(5, $statue->junction_arms);
        $this->assertSame('Bima fighting the serpent.', $statue->getTranslation('description', 'en'));
        $this->assertSame([
            ['title' => 'NusaBali - Berita', 'url' => 'https://www.nusabali.com/berita/1'],
            ['title' => 'Catatan lapangan tim', 'url' => null],
        ], $statue->sources);
        $this->assertNull(Statue::query()->firstWhere('source_no', 7)->junction_type_id);
    }

    public function test_reimporting_updates_rows_and_keeps_their_slug(): void
    {
        $this->artisan('patung:import', ['file' => $this->csv([$this->row(['name' => 'Patung Lama'])])])->assertSuccessful();
        $this->artisan('patung:import', ['file' => $this->csv([$this->row(['name' => 'Patung Baru'])])])->assertSuccessful();

        $this->assertDatabaseCount('statues', 1);
        $this->assertDatabaseHas('statues', ['name' => 'Patung Baru', 'slug' => 'patung-lama']);
    }

    public function test_statues_with_the_same_name_get_distinct_slugs(): void
    {
        $path = $this->csv([
            $this->row(['no' => '1', 'district' => 'Kuta']),
            $this->row(['no' => '2', 'district' => 'Kuta Selatan']),
        ]);

        $this->artisan('patung:import', ['file' => $path])->assertSuccessful();

        $this->assertEqualsCanonicalizing(
            ['patung-dewa-ruci', 'patung-dewa-ruci-kuta-selatan'],
            Statue::query()->pluck('slug')->all(),
        );
    }

    public function test_rejects_the_whole_file_when_any_row_is_invalid(): void
    {
        $path = $this->csv([
            $this->row(['no' => '1']),
            $this->row(['no' => '2', 'latitude' => '-6.2000000']),
            $this->row(['no' => '3', 'junction_type' => 'Simpang aneh']),
            $this->row(['no' => '4', 'regency' => 'Sleman']),
            $this->row(['no' => '5', 'sources' => 'Tautan berbahaya [javascript:alert(1)]']),
            $this->row(['no' => '6', 'statue_type' => 'Modern']),
        ]);

        $exitCode = Artisan::call('patung:import', ['file' => $path]);
        $output = Artisan::output();

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Baris 3: Latitude -6.2000000 berada di luar Bali', $output);
        $this->assertStringContainsString('Baris 4: Jenis persimpangan "Simpang aneh" tidak dikenal', $output);
        $this->assertStringContainsString('Baris 5: Kabupaten "Sleman" tidak dikenal', $output);
        $this->assertStringContainsString('Baris 6: URL referensi "javascript:alert(1)" tidak valid', $output);
        $this->assertStringContainsString('Baris 7: Jenis patung "Modern" tidak dikenal', $output);
        $this->assertDatabaseCount('statues', 0);
    }

    public function test_rejects_duplicate_numbers(): void
    {
        $path = $this->csv([$this->row(['no' => '9']), $this->row(['no' => '9', 'name' => 'Patung Lain'])]);

        $this->artisan('patung:import', ['file' => $path])
            ->expectsOutputToContain('Nomor 9 muncul lebih dari sekali')
            ->assertFailed();
    }

    public function test_reports_missing_columns(): void
    {
        $columns = array_values(array_diff(StatueImporter::COLUMNS, ['latitude']));

        $this->artisan('patung:import', ['file' => $this->csv([$this->row()], $columns)])
            ->expectsOutputToContain('Kolom wajib tidak ada: latitude')
            ->assertFailed();
    }

    public function test_reports_a_missing_file(): void
    {
        $this->artisan('patung:import', ['file' => 'tidak-ada.csv'])
            ->expectsOutputToContain('tidak ditemukan')
            ->assertFailed();
    }

    public function test_statues_missing_from_the_csv_are_removed(): void
    {
        $this->artisan('patung:import', ['file' => $this->csv([$this->row(['no' => '1']), $this->row(['no' => '2', 'name' => 'Patung Lain'])])])
            ->assertSuccessful();

        $this->artisan('patung:import', ['file' => $this->csv([$this->row(['no' => '1'])])])
            ->expectsOutputToContain('1 patung dihapus')
            ->assertSuccessful();

        $this->assertSame([1], Statue::query()->pluck('source_no')->all());
    }

    public function test_a_csv_without_data_rows_is_rejected_instead_of_emptying_the_site(): void
    {
        Statue::factory()->create();

        $this->artisan('patung:import', ['file' => $this->csv([])])
            ->expectsOutputToContain('tidak berisi baris data')
            ->assertFailed();

        $this->assertDatabaseCount('statues', 1);
    }

    public function test_handles_a_byte_order_mark_and_blank_lines(): void
    {
        $content = "\xEF\xBB\xBF\"".implode('","', StatueImporter::COLUMNS)."\"\r\n"
            .$this->line($this->row(['no' => '1']))."\r\n\r\n"
            .$this->line($this->row(['no' => '2', 'name' => 'Patung Lain']))."\r\n\r\n";

        $this->artisan('patung:import', ['file' => $this->rawCsv($content)])->assertSuccessful();

        $this->assertDatabaseCount('statues', 2);
    }

    public function test_keeps_semicolons_inside_source_urls(): void
    {
        $path = $this->csv([$this->row(['sources' => 'Arsip [https://example.com/a?x=1;y=2]; Lain [https://example.com/b]'])]);

        $this->artisan('patung:import', ['file' => $path])->assertSuccessful();

        $this->assertSame([
            ['title' => 'Arsip', 'url' => 'https://example.com/a?x=1;y=2'],
            ['title' => 'Lain', 'url' => 'https://example.com/b'],
        ], Statue::query()->first()->sources);
    }

    public function test_rejects_names_that_cannot_become_a_url(): void
    {
        $this->artisan('patung:import', ['file' => $this->csv([$this->row(['name' => 'ᬧᬢᬸᬂ'])])])
            ->expectsOutputToContain('huruf latin atau angka')
            ->assertFailed();
    }

    public function test_imports_the_team_proposal_and_photo_list(): void
    {
        $path = $this->csv([
            $this->row(['no' => '1', 'proposed_type' => 'Budaya + Ikonik', 'photos' => '001-1.jpg; 001-2.jpg']),
            $this->row(['no' => '2', 'name' => 'Patung Lain', 'proposed_type' => 'Ikonik', 'description_id' => '']),
        ]);

        $this->artisan('patung:import', ['file' => $path])->assertSuccessful();

        $first = Statue::query()->with('proposedType')->firstWhere('source_no', 1);
        $this->assertSame('budaya', $first->proposedType->slug);
        $this->assertTrue($first->proposed_iconic);
        $this->assertSame(['001-1.jpg', '001-2.jpg'], $first->photos);

        $second = Statue::query()->firstWhere('source_no', 2);
        $this->assertNull($second->proposed_type_id);
        $this->assertTrue($second->proposed_iconic);
        $this->assertSame('', $second->description);
    }

    public function test_rejects_two_main_types_and_unsafe_photo_names(): void
    {
        $path = $this->csv([
            $this->row(['no' => '1', 'proposed_type' => 'Religius + Budaya']),
            $this->row(['no' => '2', 'photos' => '../rahasia.jpg']),
        ]);

        $exitCode = Artisan::call('patung:import', ['file' => $path]);
        $output = Artisan::output();

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('lebih dari satu jenis utama', $output);
        $this->assertStringContainsString('../rahasia.jpg', $output);
    }

    public function test_bundled_dataset_imports_cleanly(): void
    {
        $this->artisan('patung:import')->assertSuccessful();

        $this->assertGreaterThan(0, Statue::query()->count());
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function row(array $overrides = []): array
    {
        return [
            'no' => '1',
            'name' => 'Patung Dewa Ruci',
            'regency' => 'Badung',
            'district' => 'Kuta',
            'village' => 'Kuta',
            'latitude' => '-8.7216780',
            'longitude' => '115.1827293',
            'statue_type' => '',
            'junction_type' => 'Pertigaan (3 arah)',
            'description_id' => 'Patung Bima melawan naga di tengah samudra.',
            'description_en' => '',
            'sources' => '',
            ...$overrides,
        ];
    }

    /**
     * @param  list<array<string, string>>  $rows
     * @param  list<string>  $columns
     */
    private function csv(array $rows, array $columns = StatueImporter::COLUMNS): string
    {
        $lines = [$this->line(array_combine($columns, $columns), $columns)];

        foreach ($rows as $row) {
            $lines[] = $this->line($row, $columns);
        }

        return $this->rawCsv(implode("\n", $lines)."\n");
    }

    /**
     * @param  array<string, string>  $row
     * @param  list<string>  $columns
     */
    private function line(array $row, array $columns = StatueImporter::COLUMNS): string
    {
        $handle = fopen('php://memory', 'r+');
        fputcsv($handle, array_map(fn (string $column) => $row[$column] ?? '', $columns), ',', '"', '', '');
        rewind($handle);
        $line = stream_get_contents($handle);
        fclose($handle);

        return $line;
    }

    private function rawCsv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'statues');
        $this->tempFiles[] = $path;
        file_put_contents($path, $content);

        return $path;
    }
}
