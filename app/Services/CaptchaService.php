<?php

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use RuntimeException;

/**
 * Generates an 8 character alphanumeric captcha containing both upper and
 * lower case letters and at least one digit. The plaintext value is never
 * stored in session — only a hash, plus an issuance timestamp that prevents
 * stale or replayed challenges.
 */
class CaptchaService
{
    public const SESSION_KEY = '_captcha_hash';

    public const SESSION_ISSUED_AT_KEY = '_captcha_issued_at';

    public const TTL_SECONDS = 300;

    public const LENGTH = 8;

    /**
     * Generate a fresh captcha string and stash a hash of it in the session.
     */
    public function generate(): string
    {
        $upper = $this->randomChars('ABCDEFGHJKLMNPQRSTUVWXYZ', 2);
        $lower = $this->randomChars('abcdefghjkmnpqrstuvwxyz', 2);
        $digit = $this->randomChars('23456789', 2);
        $rest = $this->randomChars('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789', self::LENGTH - 6);

        $chars = str_split($upper.$lower.$digit.$rest);
        // Cryptographically shuffle without leaking pattern.
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }

        $code = implode('', $chars);

        Session::put(self::SESSION_KEY, Hash::make($code));
        Session::put(self::SESSION_ISSUED_AT_KEY, time());

        return $code;
    }

    /**
     * Validate a user-submitted captcha against the session-stored hash. The
     * captcha is single-use: a successful validation invalidates it.
     */
    public function validate(?string $input): bool
    {
        if ($input === null || strlen($input) !== self::LENGTH) {
            return false;
        }

        if (! preg_match('/^[A-Za-z0-9]{8}$/', $input)) {
            return false;
        }

        $hash = Session::get(self::SESSION_KEY);
        $issuedAt = Session::get(self::SESSION_ISSUED_AT_KEY);

        if (! $hash || ! $issuedAt) {
            return false;
        }

        if ((time() - (int) $issuedAt) > self::TTL_SECONDS) {
            $this->forget();

            return false;
        }

        $valid = Hash::check($input, $hash);
        $this->forget();

        return $valid;
    }

    public function forget(): void
    {
        Session::forget([self::SESSION_KEY, self::SESSION_ISSUED_AT_KEY]);
    }

    /**
     * Render a PNG image of the supplied captcha code. Uses GD; intentionally
     * adds noise + line distortion to deter trivial OCR while remaining
     * accessible.
     */
    public function render(string $code): Response
    {
        if (! extension_loaded('gd') || ! function_exists('imagecreatetruecolor')) {
            Log::error('CaptchaService: GD extension not available — install php-gd to render the login captcha.');

            return response('GD extension not available', 503, [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            ]);
        }

        $width = 220;
        $height = 70;

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            throw new RuntimeException('imagecreatetruecolor() failed — GD may be misconfigured.');
        }
        imagesavealpha($image, true);

        $bg = imagecolorallocate($image, 250, 246, 240);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        // Background noise dots
        for ($i = 0; $i < 800; $i++) {
            $color = imagecolorallocatealpha(
                $image,
                random_int(120, 200),
                random_int(120, 200),
                random_int(120, 200),
                random_int(60, 110)
            );
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $color);
        }

        // Distortion lines
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocatealpha(
                $image,
                random_int(80, 160),
                random_int(80, 160),
                random_int(80, 160),
                random_int(40, 90)
            );
            imageline(
                $image,
                random_int(0, $width / 4),
                random_int(0, $height),
                random_int($width * 3 / 4, $width),
                random_int(0, $height),
                $color
            );
        }

        $chars = str_split($code);
        $x = 14;
        foreach ($chars as $ch) {
            $color = imagecolorallocate(
                $image,
                random_int(20, 90),
                random_int(20, 90),
                random_int(20, 120)
            );
            $size = random_int(4, 5);
            $y = random_int(20, 45);
            imagestring($image, $size, $x, $y, $ch, $color);
            $x += random_int(22, 26);
        }

        ob_start();
        $written = imagepng($image);
        $payload = (string) ob_get_clean();
        imagedestroy($image);

        if (! $written || $payload === '' || ! str_starts_with($payload, "\x89PNG\r\n\x1a\n")) {
            Log::error('CaptchaService: imagepng() produced empty or invalid PNG payload.');

            return response('Captcha rendering failed', 503, [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            ]);
        }

        return response($payload, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'Pragma' => 'no-cache',
        ]);
    }

    private function randomChars(string $alphabet, int $count): string
    {
        $out = '';
        $max = strlen($alphabet) - 1;
        for ($i = 0; $i < $count; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }

        return $out;
    }
}
