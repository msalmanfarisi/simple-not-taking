<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShareController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Public share — slug pattern: /{id}-{slug}.html
Route::get('/{id}-{slug}.html', [ShareController::class, 'show'])
    ->where(['id' => '[0-9]+', 'slug' => '[A-Za-z0-9\-]+'])
    ->name('share.show');

Route::post('/{id}-{slug}.html/unlock', [ShareController::class, 'unlock'])
    ->where(['id' => '[0-9]+', 'slug' => '[A-Za-z0-9\-]+'])
    ->name('share.unlock');

// Captcha image — must be accessible without auth.
// Path intentionally has no .png suffix: many nginx/apache configs short-circuit
// requests for static-asset extensions (png/jpg/css/...) with try_files $uri =404
// before they reach PHP, which would 404 a dynamically-generated captcha.
Route::get('/captcha', CaptchaController::class)
    ->middleware('throttle:60,1')
    ->name('captcha.image');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:20,1');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Authenticated app
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile (self-service)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Notes
    Route::resource('notes', NoteController::class);
    Route::get('notes/{note}/attachments/{attachment}', [NoteController::class, 'downloadAttachment'])
        ->name('notes.attachments.download');
    Route::delete('notes/{note}/attachments/{attachment}', [NoteController::class, 'deleteAttachment'])
        ->name('notes.attachments.destroy');

    // Admin users
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class)->except(['show']);
    });
});
