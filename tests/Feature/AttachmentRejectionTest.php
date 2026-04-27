<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentRejectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_exe_attachment_is_rejected_with_validation_error(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('Sup3rStr0ng!Pass'),
            'role' => 'user',
        ]);

        $exe = UploadedFile::fake()->createWithContent('evil.exe', 'MZdummy');

        $response = $this->actingAs($user)->post('/notes', [
            'title' => 'Note',
            'body' => 'Body',
            'attachments' => [$exe],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['attachments.0']);
        $this->assertSame(0, $user->fresh()->notes()->count(), 'No note should be persisted on rejection');
    }
}
