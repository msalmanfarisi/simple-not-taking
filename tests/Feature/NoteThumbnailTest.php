<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NoteThumbnailTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_creation_resizes_thumbnail_to_webp(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('Sup3rStr0ng!Pass'),
            'role' => 'user',
        ]);

        $jpeg = UploadedFile::fake()->image('cover.jpg', 2000, 1500);
        $txt = UploadedFile::fake()->createWithContent('notes.txt', "hello\n");

        $response = $this->actingAs($user)->post('/notes', [
            'title' => 'Pancake',
            'body' => 'Adonan pisang.',
            'thumbnail' => $jpeg,
            'attachments' => [$txt],
        ]);

        $response->assertRedirect();
        $note = $user->fresh()->notes()->first();
        $this->assertNotNull($note, 'Note should be created');
        $this->assertNotNull($note->thumbnail_path, 'Thumbnail path should be persisted');
        $this->assertStringEndsWith('.webp', $note->thumbnail_path);
        Storage::disk('public')->assertExists($note->thumbnail_path);
        $this->assertSame(1, $note->attachments()->count());
        $this->assertSame('notes.txt', $note->attachments()->first()->original_name);
    }
}
