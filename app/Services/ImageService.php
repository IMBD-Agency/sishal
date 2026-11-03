<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageService
{
    /**
     * Scale down, compress, and save an uploaded image (automatically converted to WebP).
     *
     * Supports both Intervention Image (if installed) and PHP's native GD library
     * (which works out of the box on cPanel without running composer update).
     * If all processing fails, gracefully falls back to direct raw file upload.
     *
     * @param UploadedFile $file The uploaded file object
     * @param string $directory Relative directory from public_path() (e.g., 'uploads/products')
     * @param string|null $filename Custom file name (generates unique name if null)
     * @param int|null $maxWidth Maximum width (null to skip width scale down)
     * @param int|null $maxHeight Maximum height (null to skip height scale down)
     * @param int $quality Quality factor for saving (1-100, default 80)
     * @param bool $cropSquare Whether to crop to 1:1 square
     * @param bool $convertToWebp Whether to convert the image to WebP format (default true)
     * @return string Relative path to public_path() for database storage (e.g., 'uploads/products/xyz.webp')
     */
    public static function compressAndSave(
        UploadedFile $file,
        string $directory,
        ?string $filename = null,
        ?int $maxWidth = 1200,
        ?int $maxHeight = 1200,
        int $quality = 80,
        bool $cropSquare = false,
        bool $convertToWebp = true
    ): string {
        // Normalize directory path to not have leading/trailing slashes
        $directory = trim($directory, '/');
        $absoluteDir = public_path($directory);

        // Ensure target directory exists
        if (!file_exists($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $origExt = strtolower($file->getClientOriginalExtension());
        $useWebp = $convertToWebp && function_exists('imagewebp');
        $targetExt = $useWebp ? 'webp' : $origExt;

        // Generate filename if not provided
        if (!$filename) {
            $filename = time() . '_' . uniqid() . '.' . $targetExt;
        } else {
            // If filename has original extension, adapt to target extension
            $pathInfo = pathinfo($filename);
            $filename = $pathInfo['filename'] . '.' . $targetExt;
        }

        $fullPath = $absoluteDir . '/' . $filename;
        $relativePath = $directory . '/' . $filename;

        try {
            // -------------------------------------------------------------
            // METHOD 1: Try Intervention Image (if composer package is present)
            // -------------------------------------------------------------
            if (class_exists(\Intervention\Image\Laravel\Facades\Image::class)) {
                $img = \Intervention\Image\Laravel\Facades\Image::read($file);

                if ($cropSquare) {
                    $size = min($maxWidth ?? 800, $maxHeight ?? 800);
                    $img->cover($size, $size);
                } elseif ($maxWidth || $maxHeight) {
                    $img->scaleDown(width: $maxWidth, height: $maxHeight);
                }

                if ($useWebp) {
                    $img->toWebp($quality)->save($fullPath);
                } else {
                    $img->save($fullPath, quality: $quality);
                }

                return $relativePath;
            }

            // -------------------------------------------------------------
            // METHOD 2: Native PHP GD (Zero-dependency, works directly on cPanel)
            // -------------------------------------------------------------
            if (extension_loaded('gd')) {
                $sourceImage = null;
                $filePath = $file->getRealPath();

                if ($origExt === 'jpeg' || $origExt === 'jpg') {
                    $sourceImage = @imagecreatefromjpeg($filePath);
                } elseif ($origExt === 'png') {
                    $sourceImage = @imagecreatefrompng($filePath);
                } elseif ($origExt === 'webp' && function_exists('imagecreatefromwebp')) {
                    $sourceImage = @imagecreatefromwebp($filePath);
                } elseif ($origExt === 'gif') {
                    $sourceImage = @imagecreatefromgif($filePath);
                }

                // Fallback attempt from binary string if extension loaders fail
                if (!$sourceImage) {
                    $fileContent = @file_get_contents($filePath);
                    if ($fileContent) {
                        $sourceImage = @imagecreatefromstring($fileContent);
                    }
                }

                if ($sourceImage) {
                    $origWidth = imagesx($sourceImage);
                    $origHeight = imagesy($sourceImage);

                    $srcX = 0;
                    $srcY = 0;
                    $srcW = $origWidth;
                    $srcH = $origHeight;

                    if ($cropSquare) {
                        $cropSize = min($origWidth, $origHeight);
                        $srcX = (int) (($origWidth - $cropSize) / 2);
                        $srcY = (int) (($origHeight - $cropSize) / 2);
                        $srcW = $cropSize;
                        $srcH = $cropSize;
                        $dstW = min($maxWidth ?? 800, $maxHeight ?? 800, $cropSize);
                        $dstH = $dstW;
                    } elseif ($maxWidth || $maxHeight) {
                        $ratio = min(
                            $maxWidth ? ($maxWidth / $origWidth) : 1,
                            $maxHeight ? ($maxHeight / $origHeight) : 1
                        );
                        if ($ratio < 1) {
                            $dstW = (int) round($origWidth * $ratio);
                            $dstH = (int) round($origHeight * $ratio);
                        } else {
                            $dstW = $origWidth;
                            $dstH = $origHeight;
                        }
                    } else {
                        $dstW = $origWidth;
                        $dstH = $origHeight;
                    }

                    $dstImage = imagecreatetruecolor($dstW, $dstH);

                    // Preserve transparency for PNG and WebP
                    imagealphablending($dstImage, false);
                    imagesavealpha($dstImage, true);
                    $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                    imagefilledrectangle($dstImage, 0, 0, $dstW, $dstH, $transparent);

                    imagecopyresampled($dstImage, $sourceImage, 0, 0, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);

                    if ($useWebp) {
                        imagewebp($dstImage, $fullPath, $quality);
                    } elseif ($origExt === 'png') {
                        imagepng($dstImage, $fullPath, (int) round((100 - $quality) / 10));
                    } else {
                        imagejpeg($dstImage, $fullPath, $quality);
                    }

                    imagedestroy($sourceImage);
                    imagedestroy($dstImage);

                    return $relativePath;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Image compression/WebP conversion failed: ' . $e->getMessage() . '. Falling back to raw file move.', [
                'file' => $file->getClientOriginalName(),
                'directory' => $directory
            ]);
        }

        // -------------------------------------------------------------
        // METHOD 3: Fallback - Raw File Move
        // -------------------------------------------------------------
        $rawFilename = time() . '_' . uniqid() . '.' . $origExt;
        $file->move($absoluteDir, $rawFilename);
        return $directory . '/' . $rawFilename;
    }
}
