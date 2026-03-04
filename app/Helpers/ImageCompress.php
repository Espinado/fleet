<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Сжатие фото при загрузке: уменьшает размер файла и ускоряет загрузку.
 * Сохранённое изображение можно открывать в полном размере (в пределах maxWidth).
 */
class ImageCompress
{
    /** Максимальная сторона в пикселях (длинная сторона). */
    public const MAX_SIDE = 1920;

    /** Качество JPEG (1–100). */
    public const JPEG_QUALITY = 85;

    /** MIME-типы, которые считаем изображениями для сжатия. */
    private const IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Сохраняет файл: если это фото — сжимает и сохраняет как JPEG; иначе — как есть.
     * Возвращает путь относительно диска (например для 'public').
     */
    public static function storeUpload(UploadedFile $file, string $directory, string $disk = 'public'): ?string
    {
        $mime = $file->getMimeType();
        if (!in_array($mime, self::IMAGE_MIMES, true)) {
            return $file->store($directory, $disk);
        }

        $tmpPath = $file->getRealPath();
        $image = self::loadImage($tmpPath, $mime);
        if (!$image) {
            return $file->store($directory, $disk);
        }

        $image = self::fixOrientation($image, $tmpPath, $mime);
        if (!$image) {
            return $file->store($directory, $disk);
        }

        $resized = self::resizeToMaxSide($image, self::MAX_SIDE);
        if (!$resized) {
            imagedestroy($image);
            return $file->store($directory, $disk);
        }

        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.jpg';
        $path = rtrim($directory, '/') . '/' . uniqid('', true) . '_' . \Illuminate\Support\Str::slug($filename) . '.jpg';
        $fullPath = Storage::disk($disk)->path($path);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $saved = imagejpeg($resized, $fullPath, self::JPEG_QUALITY);
        imagedestroy($image);
        imagedestroy($resized);

        if (!$saved) {
            return $file->store($directory, $disk);
        }

        return $path;
    }

    private static function loadImage(string $path, string $mime): \GdImage|false
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => false,
        };
    }

    /**
     * Коррекция ориентации по EXIF (фото с телефона часто приходят с метаданными поворота).
     * Применяет поворот/отражение к пикселям, чтобы изображение отображалось правильно везде.
     */
    private static function fixOrientation(\GdImage $image, string $path, string $mime): \GdImage|false
    {
        if ($mime !== 'image/jpeg') {
            return $image;
        }

        if (!function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path, 'IFD0', true);
        if (!is_array($exif) || !isset($exif['IFD0']['Orientation'])) {
            return $image;
        }
        $orientation = (int) $exif['IFD0']['Orientation'];
        if ($orientation <= 1 || $orientation > 8) {
            return $image;
        }

        $rotated = null;
        switch ($orientation) {
            case 2:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                return $image;
            case 3:
                $rotated = imagerotate($image, 180, 0);
                break;
            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);
                return $image;
            case 5:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                $rotated = imagerotate($image, -90, 0);
                break;
            case 6:
                $rotated = imagerotate($image, -90, 0);
                break;
            case 7:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                $rotated = imagerotate($image, 90, 0);
                break;
            case 8:
                $rotated = imagerotate($image, 90, 0);
                break;
            default:
                return $image;
        }

        if (!$rotated instanceof \GdImage) {
            return $image;
        }
        imagedestroy($image);
        return $rotated;
    }

    private static function resizeToMaxSide(\GdImage $image, int $maxSide): \GdImage|false
    {
        $w = imagesx($image);
        $h = imagesy($image);
        if ($w <= 0 || $h <= 0) {
            return false;
        }
        $long = max($w, $h);
        if ($long <= $maxSide) {
            $out = imagecreatetruecolor($w, $h);
            if (!$out) {
                return false;
            }
            imagecopy($out, $image, 0, 0, 0, 0, $w, $h);
            return $out;
        }
        $scale = $maxSide / $long;
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);
        $out = imagecreatetruecolor($nw, $nh);
        if (!$out) {
            return false;
        }
        imagecopyresampled($out, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
        return $out;
    }
}
