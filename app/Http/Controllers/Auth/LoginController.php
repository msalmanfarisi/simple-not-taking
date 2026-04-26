<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function login(Request $request, CaptchaService $captcha): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc,strict', 'max:191'],
            'password' => ['required', 'string', 'max:255'],
            'captcha' => ['required', 'string', 'size:8', 'regex:/^[A-Za-z0-9]{8}$/'],
        ]);

        $email = strtolower(trim((string) $validated['email']));

        $throttleKey = 'login:'.Str::lower($email).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('Terlalu banyak percobaan login. Coba lagi dalam :sec detik.', ['sec' => $seconds]),
            ]);
        }

        if (! $captcha->validate($validated['captcha'])) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'captcha' => __('Captcha tidak valid. Silakan coba lagi.'),
            ]);
        }

        if (! Auth::attempt(['email' => $email, 'password' => $validated['password']], false)) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => __('Email atau password salah.'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
