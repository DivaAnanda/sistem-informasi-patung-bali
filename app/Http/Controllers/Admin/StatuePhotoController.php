<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Statue;
use App\Services\Photos\InvalidPhoto;
use App\Services\Photos\PhotoStorageException;
use App\Services\Photos\S3PhotoStorage;
use App\Services\Photos\StatuePhotoUploader;
use App\Support\StatuePhotos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StatuePhotoController extends Controller
{
    private const MAX_UPLOAD_KB = 20480;

    public function store(Request $request, Statue $adminStatue, StatuePhotoUploader $uploader): RedirectResponse|JsonResponse
    {
        $request->validate(
            ['photo' => ['required', 'file', 'max:'.self::MAX_UPLOAD_KB]],
            ['photo.required' => 'Pilih foto dulu.', 'photo.max' => 'Ukuran foto maksimal 20 MB.', 'photo.file' => 'Unggahan foto gagal.'],
        );

        try {
            $key = $uploader->upload($adminStatue, $request->file('photo'));
        } catch (InvalidPhoto|PhotoStorageException $e) {
            throw ValidationException::withMessages(['photo' => $e->getMessage()]);
        }

        // Re-read the list so photos uploaded one after another from the same page are all kept.
        $statue = Statue::withUnpublished()->findOrFail($adminStatue->id);
        $statue->photos = [...($statue->photos ?? []), $key];
        $statue->save();

        return $this->respond($request, $statue, 'Foto ditambahkan.');
    }

    public function cover(Request $request, Statue $adminStatue): RedirectResponse|JsonResponse
    {
        $photo = $this->ownedPhoto($request, $adminStatue);
        $adminStatue->photos = [$photo, ...array_values(array_diff($adminStatue->photos, [$photo]))];
        $adminStatue->save();

        return $this->respond($request, $adminStatue, 'Foto sampul diganti.');
    }

    public function destroy(Request $request, Statue $adminStatue, S3PhotoStorage $storage): RedirectResponse|JsonResponse
    {
        $photo = $this->ownedPhoto($request, $adminStatue);

        // Photos shipped with the site stay on disk (they are part of the repository); uploads are removed
        // from storage. A failed delete only leaves an orphan file, so the photo still leaves the list.
        if (StatuePhotos::isUpload($photo)) {
            foreach ([$photo, StatuePhotos::thumbnailKey($photo)] as $key) {
                rescue(fn () => $storage->delete($key), report: true);
            }
        }

        $adminStatue->photos = array_values(array_diff($adminStatue->photos, [$photo]));
        $adminStatue->save();

        return $this->respond($request, $adminStatue, 'Foto dihapus.');
    }

    /**
     * Only names already in the statue's list are accepted, which also rules out path tricks.
     */
    private function ownedPhoto(Request $request, Statue $statue): string
    {
        $photo = (string) $request->input('photo', '');
        abort_unless(in_array($photo, $statue->photos ?? [], true), 404);

        return $photo;
    }

    private function respond(Request $request, Statue $statue, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'photos' => $statue->photos]);
        }

        return redirect()->route('admin.statues.edit', $statue->id)->with('status', $message);
    }
}
