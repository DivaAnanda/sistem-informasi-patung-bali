<?php

namespace Tests\Feature;

use App\Models\Regency;
use App\Models\Statue;
use App\Services\Photos\InvalidPhoto;
use App\Services\Photos\PhotoStorageException;
use App\Services\Photos\StatuePhotoUploader;
use App\Support\StatuePhotos;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = 'https://example.storage.supabase.co/storage/v1/s3';

    private const PUBLIC_URL = 'https://example.supabase.co/storage/v1/object/public/patung';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
        config([
            'patung.photo_storage.endpoint' => self::ENDPOINT,
            'patung.photo_storage.region' => 'ap-southeast-1',
            'patung.photo_storage.bucket' => 'patung',
            'patung.photo_storage.key' => 'test-key-id',
            'patung.photo_storage.secret' => 'test-secret',
            'patung.photo_storage.public_url' => self::PUBLIC_URL,
        ]);
    }

    public function test_uploaded_photo_is_resized_stripped_and_stored_with_a_thumbnail(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $statue = $this->statue();

        $key = app(StatuePhotoUploader::class)->upload($statue, $this->jpeg(3000, 2000, orientation: 1));

        $this->assertMatchesRegularExpression('#^uploads/'.$statue->source_no.'/[a-z0-9]{20}\.jpg$#', $key);

        $requests = Http::recorded()->map(fn (array $pair) => $pair[0]);
        $this->assertCount(2, $requests);

        $full = $requests->first(fn (Request $r) => str_ends_with($r->url(), '/patung/'.$key));
        $thumb = $requests->first(fn (Request $r) => str_contains($r->url(), '/thumbs/'));
        $this->assertNotNull($full);
        $this->assertNotNull($thumb);

        foreach ([$full, $thumb] as $request) {
            $this->assertSame('PUT', $request->method());
            $this->assertStringStartsWith('AWS4-HMAC-SHA256 Credential=test-key-id/', $request->header('Authorization')[0]);
            $this->assertSame('image/jpeg', $request->header('Content-Type')[0]);
            $this->assertStringNotContainsString('Exif', $request->body());
        }

        $this->assertSame([1280, 853], $this->dimensions($full->body()));
        $this->assertSame([640, 427], $this->dimensions($thumb->body()));
    }

    public function test_exif_orientation_is_applied_before_the_tag_is_dropped(): void
    {
        Http::fake(['*' => Http::response('', 200)]);

        app(StatuePhotoUploader::class)->upload($this->statue(), $this->jpeg(800, 400, orientation: 6));

        $full = Http::recorded()->map(fn (array $pair) => $pair[0])
            ->first(fn (Request $r) => ! str_contains($r->url(), '/thumbs/'));
        $this->assertSame([400, 800], $this->dimensions($full->body()));
    }

    public function test_unsupported_files_are_rejected_before_anything_is_stored(): void
    {
        Http::fake();

        $this->expectException(InvalidPhoto::class);

        try {
            app(StatuePhotoUploader::class)->upload(
                $this->statue(),
                UploadedFile::fake()->createWithContent('foto.heic', 'not an image'),
            );
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_storage_errors_surface_as_an_exception(): void
    {
        Http::fake(['*' => Http::response('<Error><Code>AccessDenied</Code></Error>', 403)]);

        $this->expectException(PhotoStorageException::class);

        app(StatuePhotoUploader::class)->upload($this->statue(), $this->jpeg(800, 600, orientation: 1));
    }

    public function test_storage_keys_resolve_to_public_urls_and_static_files_stay_local(): void
    {
        $this->assertSame(self::PUBLIC_URL.'/uploads/101/abc.jpg', StatuePhotos::url('uploads/101/abc.jpg'));
        $this->assertSame(self::PUBLIC_URL.'/uploads/101/thumbs/abc.jpg', StatuePhotos::thumbnailUrl('uploads/101/abc.jpg'));
        $this->assertSame(asset('images/patung/001-1.jpg'), StatuePhotos::url('001-1.jpg'));
    }

    private function statue(): Statue
    {
        return Statue::factory()->create(['regency_id' => Regency::query()->value('id'), 'source_no' => 101]);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function dimensions(string $jpeg): array
    {
        $info = getimagesizefromstring($jpeg);

        return [$info[0], $info[1]];
    }

    /**
     * A JPEG with an APP1 Exif segment carrying only the orientation tag, built by hand because GD cannot write EXIF.
     */
    private function jpeg(int $width, int $height, int $orientation): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, intdiv($width, 2), $height, imagecolorallocate($image, 200, 40, 40));
        ob_start();
        imagejpeg($image, null, 90);
        $plain = (string) ob_get_clean();

        $tiff = 'II*'."\0".pack('V', 8).pack('v', 1).pack('vvVvv', 0x0112, 3, 1, $orientation, 0).pack('V', 0);
        $payload = "Exif\0\0".$tiff;
        $app1 = "\xFF\xE1".pack('n', strlen($payload) + 2).$payload;

        return UploadedFile::fake()->createWithContent('foto.jpg', substr($plain, 0, 2).$app1.substr($plain, 2));
    }
}
