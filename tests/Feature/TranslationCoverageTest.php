<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TranslationCoverageTest extends TestCase
{
    private const TRANSLATION_CALL = "/__\\('((?:[^'\\\\]|\\\\.)*)'/";

    public function test_every_interface_string_has_an_english_translation(): void
    {
        $english = json_decode(File::get(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);

        $missing = collect([resource_path('views'), app_path()])
            ->flatMap(fn (string $directory) => File::allFiles($directory))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->flatMap(fn ($file) => preg_match_all(self::TRANSLATION_CALL, $file->getContents(), $matches) ? $matches[1] : [])
            ->map(fn (string $key) => stripslashes($key))
            ->unique()
            ->reject(fn (string $key) => array_key_exists($key, $english))
            ->values()
            ->all();

        $this->assertSame([], $missing, 'Teks berikut belum punya terjemahan di lang/en.json.');
    }
}
