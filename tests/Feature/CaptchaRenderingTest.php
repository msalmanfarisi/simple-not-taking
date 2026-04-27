<?php

namespace Tests\Feature;

use App\Services\CaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaptchaRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_captcha_endpoint_returns_valid_png(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension not available in this environment.');
        }

        $response = $this->get('/captcha');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');

        $payload = $response->getContent();
        $this->assertNotEmpty($payload, 'Captcha PNG body must not be empty.');
        $this->assertTrue(
            str_starts_with($payload, "\x89PNG\r\n\x1a\n"),
            'Captcha response is not a valid PNG (magic bytes missing).'
        );

        // 220 x 70 from CaptchaService::render()
        $info = @getimagesizefromstring($payload);
        $this->assertIsArray($info);
        $this->assertSame(220, $info[0]);
        $this->assertSame(70, $info[1]);
    }

    public function test_login_view_uses_relative_captcha_url(): void
    {
        $html = $this->get('/login')->getContent();

        $this->assertStringContainsString('id="captchaImage"', $html);
        $this->assertMatchesRegularExpression(
            '#<img[^>]+id="captchaImage"[^>]+src="/captcha\?_=\d+"#',
            $html,
            'Captcha <img> must use a root-relative src so it is unaffected by APP_URL/host mismatches.'
        );
    }

    public function test_captcha_persists_hash_in_session_for_validation(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension not available in this environment.');
        }

        $this->get('/captcha')->assertOk();

        $this->assertNotNull(session(CaptchaService::SESSION_KEY));
        $this->assertNotNull(session(CaptchaService::SESSION_ISSUED_AT_KEY));
    }
}
