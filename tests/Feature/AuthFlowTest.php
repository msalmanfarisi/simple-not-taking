<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_captcha(): void
    {
        $user = User::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => Hash::make('Sup3rStr0ng!Pass'),
            'role' => 'admin',
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Sup3rStr0ng!Pass',
            'captcha' => 'AAAAaaaa',
        ])->assertSessionHasErrors('captcha');
    }

    public function test_login_succeeds_with_correct_captcha(): void
    {
        $user = User::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => Hash::make('Sup3rStr0ng!Pass'),
            'role' => 'admin',
        ]);

        $svc = $this->app->make(CaptchaService::class);
        $code = $svc->generate();

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Sup3rStr0ng!Pass',
            'captcha' => $code,
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user->refresh());
    }
}
