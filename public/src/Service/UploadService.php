<?php

declare(strict_types=1);

namespace App\Service;

final class UploadService
{
    private const MAX_BYTES = 2_097_152;

    /** @var array<string, string> */
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /** 
     * Handles storing an uploaded image file.
     * @param array<string, mixed> $file 
     */
    public static function storeImage(array $file, string $subdir): array
    {
        // No file uploaded
        if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'errors' => []];
        }

        // Some kind of upload error
        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            return ['path' => null, 'errors' => ['Upload fehlgeschlagen.']];
        }

        // File did not come from HTTP upload or tmp_name not set (security)
        if (!is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) {
            return ['path' => null, 'errors' => ['Ungültige Upload-Datei.']];
        }

        // Max size exceeded
        if ((int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            return ['path' => null, 'errors' => ['Bild: max. 2 MB.']];
        }

        // Determine MIME type using file info; only allow certain image types
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!is_string($mime) || !isset(self::ALLOWED_MIMES[$mime])) {
            return ['path' => null, 'errors' => ['Nur JPEG, PNG oder WebP erlaubt.']];
        }

        // Generate random filename and construct paths
        $filename = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_MIMES[$mime];
        $relativePath = '/uploads/' . trim($subdir, '/') . '/' . $filename;
        $publicRoot = dirname(__DIR__, 2);
        $targetDir = $publicRoot . '/uploads/' . trim($subdir, '/');

        // If uploads directory does not exist, try to create it
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            return ['path' => null, 'errors' => ['Upload-Verzeichnis nicht beschreibbar.']];
        }

        // Move the uploaded temporary file to the final destination
        if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename)) {
            return ['path' => null, 'errors' => ['Datei konnte nicht gespeichert werden.']];
        }

        return ['path' => $relativePath, 'errors' => []];
    }

    /**
     * Deletes a file by its relative path under /uploads.
     * Will only delete actual files under the uploads folder for safety.
     */
    public static function deleteFile(?string $relativePath): void
    {
        // Only delete if valid path within uploads
        if ($relativePath === null || $relativePath === '' || !str_starts_with($relativePath, '/uploads/')) {
            return;
        }

        $fullPath = dirname(__DIR__, 2) . $relativePath;
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
