<?php
namespace App\Services\Assets;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Models\Attachment;

class AssetsService {

    public function storeAttachment($file, $attachableType, $attachableId)
    {
        $originalName = $file->getClientOriginalName();

        // Ensure the file extension is valid and there is no path traversal in the file name
        if (preg_match('/\.[^.]+\./', $originalName)) {
            throw new Exception(trans('general.notAllowedAction'), 403);
        }

        // Check for path traversal attack
        if (strpos($originalName, '..') !== false || strpos($originalName, '/') !== false || strpos($originalName, '\\') !== false) {
            throw new Exception(trans('general.pathTraversalDetected'), 403);
        }

        // Validate the MIME type to ensure it's an acceptable file type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $mime_type = $file->getClientMimeType();

        if (!in_array($mime_type, $allowedMimeTypes)) {
            throw new FileException(trans('general.invalidFileType'), 403);
        }

        // Generate a safe, random file name
        $fileName = Str::random(32);
        $extension = $file->getClientOriginalExtension(); // Get the file extension
        $filePath = "attachments/{$fileName}.{$extension}";

        // Store the file securely
        //file will be uploaded to storage/app/attachements
        $stored = Storage::disk('local')->put($filePath, file_get_contents($file));
        if (!$stored) {
            throw new Exception(trans('general.failedToStoreFile'), 500);
        }

        // Create attachment record
        $attachment = Attachment::create([
            'file_name' => $originalName,
            'file_path' => $filePath,
            'mime_type' => $mime_type,
            'attachable_id' => $attachableId,
            'attachable_type' => $attachableType,
        ]);

        return $attachment;
    }
}

