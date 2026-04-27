<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_emits_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotEmpty($response->headers->get('Content-Security-Policy'));
        $this->assertNotEmpty($response->headers->get('Strict-Transport-Security'));
        $this->assertNotEmpty($response->headers->get('Permissions-Policy'));
        $this->assertNull($response->headers->get('X-Powered-By'));
        $this->assertNull($response->headers->get('Server'));
    }

    public function test_share_for_unknown_note_returns_404(): void
    {
        $this->get('/9999-nonexistent.html')->assertNotFound();
    }
}
