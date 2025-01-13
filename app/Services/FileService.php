<?php

namespace App\Services;

use App\Models\File;

class FileService
{
    /**
     * Upload multiple files and save their metadata.
     *
     * @param UploadedFile[] $files
     * @param string $itemCode
     * @return void
     */
    public function uploadFiles(array $files, $itemCode)
    {
        foreach ($files as $file) {
            $this->uploadFile($file, $itemCode);
        }
    }

    /**
     * Upload a single file and save its metadata.
     *
     * @param UploadedFile $file
     * @param string $itemCode
     * @return void
     */
    public function uploadFile($file, $itemCode)
    {
        $fileName = time() . '-' . $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $file->storeAs('public/files', $fileName);

        File::create([
            'item_code' => $itemCode,
            'name' => $fileName,
            'mime_type' => $file->getClientMimeType(),
            'size' => $fileSize,
        ]);
    }
}
