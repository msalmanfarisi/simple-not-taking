<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Note;
use App\Support\FileSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Mews\Purifier\Facades\Purifier;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $search = trim((string) $request->query('q', ''));
        $category = (int) $request->query('category', 0);

        $notes = Note::query()
            ->where('user_id', $userId)
            ->with('category')
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
                $q->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                        ->orWhere('body', 'like', $like);
                });
            })
            ->when($category > 0, fn ($q) => $q->where('category_id', $category))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('user_id', $userId)->orderBy('name')->get();

        return view('notes.index', compact('notes', 'categories', 'search', 'category'));
    }

    public function create(Request $request): View
    {
        return view('notes.create', [
            'note' => new Note,
            'categories' => Category::where('user_id', $request->user()->id)->orderBy('name')->get(),
            'allowedExt' => FileSecurity::attachmentExtensions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedPayload($request);

        $note = DB::transaction(function () use ($request, $data) {
            $note = Note::create([
                'user_id' => $request->user()->id,
                'category_id' => $data['category_id'] ?? null,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']) ?: 'catatan',
                'body' => Purifier::clean($data['body']),
                'reference_url' => $data['reference_url'] ?? null,
                'share_password_hash' => ! empty($data['share_password']) ? Hash::make($data['share_password']) : null,
                'share_expires_at' => $data['share_expires_at'] ?? null,
            ]);

            if ($request->hasFile('thumbnail')) {
                $note->thumbnail_path = $this->storeThumbnail($request->file('thumbnail'));
                $note->save();
            }

            $this->storeAttachments($request, $note);

            return $note;
        });

        return redirect()->route('notes.show', $note)->with('status', 'Catatan tersimpan.');
    }

    public function show(Request $request, Note $note): View
    {
        $this->authorizeOwner($request, $note);
        $note->load('attachments', 'category');

        return view('notes.show', compact('note'));
    }

    public function edit(Request $request, Note $note): View
    {
        $this->authorizeOwner($request, $note);
        $note->load('attachments');

        return view('notes.edit', [
            'note' => $note,
            'categories' => Category::where('user_id', $request->user()->id)->orderBy('name')->get(),
            'allowedExt' => FileSecurity::attachmentExtensions(),
        ]);
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        $this->authorizeOwner($request, $note);
        $data = $this->validatedPayload($request);

        DB::transaction(function () use ($request, $note, $data) {
            $note->category_id = $data['category_id'] ?? null;
            $note->title = $data['title'];
            $note->slug = Str::slug($data['title']) ?: 'catatan';
            $note->body = Purifier::clean($data['body']);
            $note->reference_url = $data['reference_url'] ?? null;

            if ($request->boolean('clear_share_password')) {
                $note->share_password_hash = null;
            } elseif (! empty($data['share_password'])) {
                $note->share_password_hash = Hash::make($data['share_password']);
            }

            $note->share_expires_at = $data['share_expires_at'] ?? null;

            if ($request->boolean('remove_thumbnail') && $note->thumbnail_path) {
                Storage::disk('public')->delete($note->thumbnail_path);
                $note->thumbnail_path = null;
            }

            if ($request->hasFile('thumbnail')) {
                if ($note->thumbnail_path) {
                    Storage::disk('public')->delete($note->thumbnail_path);
                }
                $note->thumbnail_path = $this->storeThumbnail($request->file('thumbnail'));
            }

            $note->save();
            $this->storeAttachments($request, $note);
        });

        return redirect()->route('notes.show', $note)->with('status', 'Catatan diperbarui.');
    }

    public function destroy(Request $request, Note $note): RedirectResponse
    {
        $this->authorizeOwner($request, $note);

        DB::transaction(function () use ($note) {
            foreach ($note->attachments as $att) {
                Storage::disk('local')->delete($att->stored_path);
            }
            if ($note->thumbnail_path) {
                Storage::disk('public')->delete($note->thumbnail_path);
            }
            $note->delete();
        });

        return redirect()->route('notes.index')->with('status', 'Catatan dihapus.');
    }

    public function deleteAttachment(Request $request, Note $note, Attachment $attachment): RedirectResponse
    {
        $this->authorizeOwner($request, $note);
        if ($attachment->note_id !== $note->id) {
            abort(404);
        }
        Storage::disk('local')->delete($attachment->stored_path);
        $attachment->delete();

        return back()->with('status', 'Lampiran dihapus.');
    }

    public function downloadAttachment(Request $request, Note $note, Attachment $attachment): Response
    {
        $this->authorizeOwner($request, $note);
        if ($attachment->note_id !== $note->id) {
            abort(404);
        }
        if (! Storage::disk('local')->exists($attachment->stored_path)) {
            abort(404);
        }

        return response(
            Storage::disk('local')->get($attachment->stored_path),
            200,
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'attachment; filename="'.addslashes($attachment->original_name).'"',
                'Content-Length' => (string) $attachment->size_bytes,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }

    private function validatedPayload(Request $request): array
    {
        $userId = $request->user()->id;
        $allowed = FileSecurity::attachmentExtensionsCsv();

        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:200000'],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id,user_id,'.$userId,
            ],
            'reference_url' => ['nullable', 'url:http,https', 'max:2048'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:8192'],
            'attachments' => ['nullable', 'array', 'max:20'],
            'attachments.*' => ['file', 'mimes:'.$allowed, 'max:'.(FileSecurity::MAX_ATTACHMENT_BYTES / 1024)],
            'share_password' => ['nullable', 'string', 'min:8', 'max:128'],
            'share_expires_at' => ['nullable', 'date', 'after:now'],
        ]);
    }

    private function storeThumbnail(UploadedFile $file): string
    {
        FileSecurity::assertSafeImage($file);
        $img = ImageManager::usingDriver(GdDriver::class)
            ->decodePath($file->getRealPath())
            ->scaleDown(1200, 1200);
        $filename = 'thumbnails/'.Str::uuid()->toString().'.webp';
        Storage::disk('public')->put($filename, (string) $img->encode(new WebpEncoder(quality: 82)));

        return $filename;
    }

    private function storeAttachments(Request $request, Note $note): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        foreach ((array) $request->file('attachments') as $file) {
            if (! $file) {
                continue;
            }
            FileSecurity::assertSafeAttachment($file);
            $ext = strtolower($file->getClientOriginalExtension());
            $stored = 'attachments/'.$note->id.'/'.Str::uuid()->toString().'.'.$ext;
            Storage::disk('local')->putFileAs(
                dirname($stored),
                $file,
                basename($stored)
            );

            Attachment::create([
                'note_id' => $note->id,
                'original_name' => FileSecurity::safeName($file),
                'stored_path' => $stored,
                'mime_type' => (string) $file->getMimeType(),
                'extension' => $ext,
                'size_bytes' => (int) $file->getSize(),
            ]);
        }
    }

    private function authorizeOwner(Request $request, Note $note): void
    {
        if ($note->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
