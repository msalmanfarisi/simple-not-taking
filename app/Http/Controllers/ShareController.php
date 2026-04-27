<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ShareController extends Controller
{
    /**
     * Public share endpoint matching: /{id}-{slug}.html
     * The slug acts as a soft canonicalisation but {id} is authoritative.
     */
    public function show(Request $request, int $id, string $slug): View|RedirectResponse|Response
    {
        $note = Note::with('attachments', 'category', 'user')->find($id);

        if (! $note) {
            abort(404);
        }

        if ($note->slug !== $slug) {
            return redirect()->route('share.show', ['id' => $note->id, 'slug' => $note->slug], 301);
        }

        if ($note->isExpired()) {
            return response()->view('share.expired', ['note' => $note], 410);
        }

        if ($note->requiresPassword() && ! $this->hasUnlocked($request, $note)) {
            return view('share.password', ['note' => $note]);
        }

        return view('share.show', ['note' => $note]);
    }

    public function unlock(Request $request, int $id, string $slug): RedirectResponse
    {
        $note = Note::find($id);
        if (! $note || $note->slug !== $slug) {
            abort(404);
        }
        if ($note->isExpired()) {
            return redirect()->route('share.show', ['id' => $note->id, 'slug' => $note->slug]);
        }
        if (! $note->requiresPassword()) {
            return redirect()->route('share.show', ['id' => $note->id, 'slug' => $note->slug]);
        }

        $key = 'share-unlock:'.$note->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'password' => __('Terlalu banyak percobaan. Coba lagi dalam :sec detik.', ['sec' => $seconds]),
            ]);
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'max:128'],
        ]);

        if (! Hash::check($data['password'], (string) $note->share_password_hash)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'password' => __('Password salah.'),
            ]);
        }

        RateLimiter::clear($key);
        $unlocked = (array) $request->session()->get('share_unlocked', []);
        $unlocked[] = $note->id;
        $request->session()->put('share_unlocked', array_values(array_unique($unlocked)));

        return redirect()->route('share.show', ['id' => $note->id, 'slug' => $note->slug]);
    }

    private function hasUnlocked(Request $request, Note $note): bool
    {
        $unlocked = (array) $request->session()->get('share_unlocked', []);

        return in_array($note->id, $unlocked, true);
    }
}
