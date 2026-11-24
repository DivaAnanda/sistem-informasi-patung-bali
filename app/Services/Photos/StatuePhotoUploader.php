<?php

namespace App\Services\Photos;

use App\Models\Statue;
use App\Support\StatuePhotos;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class StatuePhotoUploader
{
    public function __construct(
        private readonly PhotoProcessor $processor,
        private readonly S3PhotoStorage $storage,
    ) {}

    /**
     * Stores the photo and its thumbnail, and returns the key to append to the statue's photos list.
     */
    public function upload(Statue $statue, UploadedFile $file): string
    {
        $images = $this->processor->process($file->getRealPath());
        $key = StatuePhotos::UPLOAD_PREFIX.$statue->source_no.'/'.Str::lower(Str::random(20)).'.jpg';

        $this->storage->put($key, $images['full'], 'image/jpeg');

        try {
            $this->storage->put(StatuePhotos::thumbnailKey($key), $images['thumb'], 'image/jpeg');
        } catch (PhotoStorageException $e) {
            rescue(fn () => $this->storage->delete($key), report: true);

            throw $e;
        }

        return $key;
    }
}
