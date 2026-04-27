<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Defensive helpers for validating user-supplied files. Validates by
 * actual MIME (not the user-controlled extension), bounds the size, and
 * rejects anything that does not match the documented allow lists.
 */
final class FileSecurity
{
    /** Image extensions/MIME types accepted for thumbnails and avatars. */
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public const IMAGE_MIMES = ['image/jpeg', 'image/pjpeg', 'image/png'];

    /**
     * Allow list for note attachments. Mapped to canonical MIME types so a
     * spoofed extension cannot smuggle in an executable payload.
     */
    public const ATTACHMENT_MIMES = [
        'txt' => ['text/plain'],
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png' => ['image/png'],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'application/octet-stream',
        ],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream',
        ],
    ];

    public const MAX_ATTACHMENT_BYTES = 16 * 1024 * 1024; // 16 MiB

    public const MAX_IMAGE_BYTES = 8 * 1024 * 1024;  // 8 MiB

    public static function attachmentExtensions(): array
    {
        return array_keys(self::ATTACHMENT_MIMES);
    }

    public static function attachmentExtensionsCsv(): string
    {
        return implode(',', self::attachmentExtensions());
    }

    public static function assertSafeImage(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new FileException('Upload tidak valid.');
        }
        if ($file->getSize() > self::MAX_IMAGE_BYTES) {
            throw new FileException('Ukuran gambar terlalu besar.');
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();

        if (! in_array($ext, self::IMAGE_EXTENSIONS, true)) {
            throw new FileException('Ekstensi gambar tidak didukung.');
        }
        if (! in_array($mime, self::IMAGE_MIMES, true)) {
            throw new FileException('Tipe MIME gambar tidak cocok.');
        }
        if (@getimagesize($file->getRealPath()) === false) {
            throw new FileException('File bukan gambar yang valid.');
        }
    }

    public static function assertSafeAttachment(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new FileException('Upload tidak valid.');
        }
        if ($file->getSize() > self::MAX_ATTACHMENT_BYTES) {
            throw new FileException('Ukuran lampiran terlalu besar.');
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();

        if (! array_key_exists($ext, self::ATTACHMENT_MIMES)) {
            throw new FileException('Ekstensi lampiran tidak diizinkan.');
        }
        if (! in_array($mime, self::ATTACHMENT_MIMES[$ext], true)) {
            throw new FileException('Tipe MIME lampiran tidak cocok dengan ekstensinya.');
        }
    }

    public static function safeName(UploadedFile $file): string
    {
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9_\- ]+/', '', (string) $base) ?? '';
        $base = trim($base) !== '' ? substr(trim($base), 0, 80) : 'file';

        return $base.'.'.strtolower($file->getClientOriginalExtension());
    }
}
