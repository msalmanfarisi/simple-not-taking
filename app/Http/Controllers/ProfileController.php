<?php

namespace App\Http\Controllers;

use App\Support\FileSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc,strict', 'max:191', 'unique:users,email,'.$user->id],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        $user->name = strip_tags($data['name']);
        $user->email = strtolower($data['email']);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            FileSecurity::assertSafeImage($file);

            $filename = 'avatars/'.Str::uuid()->toString().'.webp';
            $img = Image::read($file->getRealPath())->cover(256, 256);
            Storage::disk('public')->put($filename, (string) $img->toWebp(85));

            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = $filename;
        }

        $user->save();

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()],
        ]);

        $user->password = Hash::make($data['password']);
        $user->save();

        $request->session()->regenerate();

        return back()->with('status', 'Password berhasil diperbarui.');
    }
}
