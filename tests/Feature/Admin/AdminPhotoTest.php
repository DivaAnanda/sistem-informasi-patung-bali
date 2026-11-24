<?php

namespace Tests\Feature\Admin;

use App\Models\Statue;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class AdminPhotoTest extends AdminTestCase
{
    public function test_uploaded_photo_is_appended_to_the_statue(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $statue = $this->statue(['photos' => ['001-1.jpg']]);

        $this->logIn()
            ->post('/admin/patung/'.$statue->id.'/foto', ['photo' => $this->jpeg()])
            ->assertRedirect('/admin/patung/'.$statue->id);

        $photos = Statue::withUnpublished()->findOrFail($statue->id)->photos;
        $this->assertCount(2, $photos);
        $this->assertSame('001-1.jpg', $photos[0]);
        $this->assertStringStartsWith('uploads/'.$statue->source_no.'/', $photos[1]);
    }

    public function test_fetch_uploads_get_json_back(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $statue = $this->statue();

        $this->logIn()
            ->postJson('/admin/patung/'.$statue->id.'/foto', ['photo' => $this->jpeg()])
            ->assertOk()
            ->assertJsonCount(1, 'photos');
    }

    public function test_unusable_files_come_back_as_a_form_error(): void
    {
        Http::fake();
        $statue = $this->statue();

        $this->logIn()
            ->from('/admin/patung/'.$statue->id)
            ->post('/admin/patung/'.$statue->id.'/foto', ['photo' => UploadedFile::fake()->createWithContent('a.heic', 'x')])
            ->assertSessionHasErrors(['photo' => 'Format foto harus JPG, PNG, atau WebP. Foto HEIC dari iPhone perlu diubah ke JPG dulu.']);

        Http::assertNothingSent();
    }

    public function test_any_photo_can_become_the_cover(): void
    {
        $statue = $this->statue(['photos' => ['001-1.jpg', '001-2.jpg', '001-3.jpg']]);

        $this->logIn()->post('/admin/patung/'.$statue->id.'/foto/sampul', ['photo' => '001-3.jpg']);

        $this->assertSame(['001-3.jpg', '001-1.jpg', '001-2.jpg'], Statue::withUnpublished()->findOrFail($statue->id)->photos);
    }

    public function test_removing_an_upload_deletes_it_from_storage_and_a_shipped_photo_only_from_the_list(): void
    {
        Http::fake(['*' => Http::response('', 204)]);
        $statue = $this->statue(['photos' => ['001-1.jpg', 'uploads/7/abc.jpg']]);

        $this->logIn()->delete('/admin/patung/'.$statue->id.'/foto', ['photo' => 'uploads/7/abc.jpg']);
        $this->delete('/admin/patung/'.$statue->id.'/foto', ['photo' => '001-1.jpg']);

        $this->assertSame([], Statue::withUnpublished()->findOrFail($statue->id)->photos);
        $deleted = Http::recorded()->map(fn (array $pair) => $pair[0])->filter(fn (Request $r) => $r->method() === 'DELETE');
        $this->assertCount(2, $deleted);
        $this->assertTrue($deleted->contains(fn (Request $r) => str_ends_with($r->url(), '/patung/uploads/7/abc.jpg')));
        $this->assertTrue($deleted->contains(fn (Request $r) => str_ends_with($r->url(), '/patung/uploads/7/thumbs/abc.jpg')));
        $this->assertFileExists(public_path('images/patung/001-1.jpg'));
    }

    public function test_photo_actions_only_touch_photos_the_statue_has(): void
    {
        Http::fake();
        $statue = $this->statue(['photos' => ['001-1.jpg']]);

        $this->logIn()->delete('/admin/patung/'.$statue->id.'/foto', ['photo' => 'uploads/9/lain.jpg'])->assertNotFound();
        $this->post('/admin/patung/'.$statue->id.'/foto/sampul', ['photo' => '../../.env'])->assertNotFound();

        Http::assertNothingSent();
    }

    private function jpeg(): UploadedFile
    {
        $image = imagecreatetruecolor(900, 600);
        ob_start();
        imagejpeg($image);

        return UploadedFile::fake()->createWithContent('foto.jpg', (string) ob_get_clean());
    }
}
