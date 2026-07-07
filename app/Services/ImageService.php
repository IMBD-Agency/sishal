<?php

namespace App\Services;

use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageService
{
    /**
     * Scale down and compress an uploaded image, saving it to the specified public directory.
     * If the image processing fails or requirements are missing, falls back to direct raw file upload.
     *
     * @param UploadedFile $file The uploaded file object
     * @param string $directory Relative directory from public_path() (e.g., 'uploads/products')
     * @param string|null $filename Custom file name (generates unique name if null)
     * @param int|null $maxWidth Maximum width (null to skip width scale down)
     * @param int|null $maxHeight Maximum height (null to skip height scale down)
     * @param int $quality Quality factor for saving (1-100)
     * @return string Relative path to public_path() for database storage (e.g., 'uploads/products/xyz.jpg')
     */
    public static function compressAndSave(
        UploadedFile $file,
        string $directory,
        ?string $filename = null,
        ?int $maxWidth = 1200,
        ?int $maxHeight = 1200,
        int $quality = 80,
        bool $cropSquare = false
    ): string {
        // Normalize directory path to not have leading/trailing slashes
        $directory = trim($directory, '/');
        $absoluteDir = public_path($directory);

        // Ensure target directory exists
        if (!file_exists($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        // Generate filename if not provided
        if (!$filename) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        }

        $fullPath = $absoluteDir . '/' . $filename;
        $relativePath = $directory . '/' . $filename;

        try {
            // Read image via Intervention Image
            $img = Image::read($file);

            // If 1:1 square crop is requested, use cover to resize and crop to square
            if ($cropSquare) {
                $size = min($maxWidth ?? 800, $maxHeight ?? 800);
                $img->cover($size, $size);
            } elseif ($maxWidth || $maxHeight) {
                // Scale down to max dimensions if provided, maintaining aspect ratio without upscaling
                $img->scaleDown(width: $maxWidth, height: $maxHeight);
            }

            // Save using detected extension and specified quality
            $img->save($fullPath, quality: $quality);
        } catch (\Throwable $e) {
            // Log fallback warning and move original raw file directly
            Log::warning('Image compression failed: ' . $e->getMessage() . '. Falling back to raw file move.', [
                'file' => $file->getClientOriginalName(),
                'directory' => $directory
            ]);

            $file->move($absoluteDir, $filename);
        }

        return $relativePath;
    }
}
