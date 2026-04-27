<?php

namespace Tests\Unit;

use App\Services\CaptchaService;
use Tests\TestCase;

class CaptchaServiceTest extends TestCase
{
    public function test_generates_eight_alphanumeric_with_mixed_case_and_digit(): void
    {
        $svc = new CaptchaService;
        for ($i = 0; $i < 50; $i++) {
            $code = $svc->generate();
            $this->assertSame(8, strlen($code));
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{8}$/', $code);
            $this->assertMatchesRegularExpression('/[A-Z]/', $code, 'no uppercase');
            $this->assertMatchesRegularExpression('/[a-z]/', $code, 'no lowercase');
            $this->assertMatchesRegularExpression('/[0-9]/', $code, 'no digit');
        }
    }

    public function test_validates_session_stored_code_once_only(): void
    {
        $svc = new CaptchaService;
        $code = $svc->generate();

        $this->assertTrue($svc->validate($code), 'first validate should pass');
        $this->assertFalse($svc->validate($code), 'should be single-use');
    }

    public function test_rejects_wrong_length_or_charset(): void
    {
        $svc = new CaptchaService;
        $svc->generate();

        $this->assertFalse($svc->validate('short'));
        $this->assertFalse($svc->validate('12345678!'));
        $this->assertFalse($svc->validate(null));
    }
}
