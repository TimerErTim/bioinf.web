<?php

declare(strict_types=1);

namespace App\Service;

/*
 * Server-side image upload handling for quotes and avatars.
 * Validates MIME type via finfo, enforces size limit, stores with random filename.
 */
final class UploadService
{
    private const MAX_BYTES = 2_097_152; // 2 MB

    /** @var list<string> */
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * @param array<string, mixed> $file  One entry from $_FILES
     * @return array{path: string|null, errors: list<string>}
     */
    public static function storeImage(array $file, string $subdir): array
    {
        if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'errors' => []];
        }

        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            return ['path' => null, 'errors' => ['Upload fehlgeschlagen.']];
        }

        if (!is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) {
            return ['path' => null, 'errors' => ['Ungültige Upload-Datei.']];
        }

        if ((int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            return ['path' => null, 'errors' => ['Bild: max. 2 MB.']];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!is_string($mime) || !isset(self::ALLOWED_MIMES[$mime])) {
            return ['path' => null, 'errors' => ['Nur JPEG, PNG oder WebP erlaubt.']];
        }

        $ext = self::ALLOWED_MIMES[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $relativePath = '/uploads/' . trim($subdir, '/') . '/' . $filename;

        $publicRoot = dirname(__DIR__, 2);
        $targetDir = $publicRoot . '/uploads/' . trim($subdir, '/');
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            return ['path' => null, 'errors' => ['Upload-Verzeichnis nicht beschreibbar.']];
        }

        $targetPath = $targetDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['path' => null, 'errors' => ['Datei konnte nicht gespeichert werden.']];
        }

        return ['path' => $relativePath, 'errors' => []];
    }

    public static function deleteFile(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        if (!str_starts_with($relativePath, '/uploads/')) {
            return;
        }

        $publicRoot = dirname(__DIR__, 2);
        $fullPath = $publicRoot . $relativePath;
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
