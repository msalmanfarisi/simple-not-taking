<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mews\Purifier\Facades\Purifier;
use Tests\TestCase;

class AutoLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_purifier_linkifies_plain_urls_with_target_blank_and_safe_rel(): void
    {
        $clean = Purifier::clean('Lihat https://example.com/path?q=1 untuk detail.');

        $this->assertStringContainsString('<a href="https://example.com/path?q=1"', $clean);
        $this->assertStringContainsString('target="_blank"', $clean);
        $this->assertStringContainsString('noopener', $clean);
        $this->assertStringContainsString('noreferrer', $clean);
    }

    public function test_purifier_forces_target_blank_on_existing_anchor(): void
    {
        $clean = Purifier::clean('<a href="https://example.com">click</a>');

        $this->assertStringContainsString('target="_blank"', $clean);
        $this->assertStringContainsString('noopener', $clean);
        $this->assertStringContainsString('noreferrer', $clean);
    }

    public function test_purifier_strips_javascript_scheme(): void
    {
        $clean = Purifier::clean('<a href="javascript:alert(1)">x</a>');

        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function test_note_body_html_accessor_linkifies_legacy_plain_urls(): void
    {
        $user = User::factory()->create();
        $note = Note::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'slug' => 'test',
            'body' => '<p>Visit https://example.com today.</p>',
        ]);

        $rendered = $note->body_html;

        $this->assertStringContainsString('href="https://example.com"', $rendered);
        $this->assertStringContainsString('target="_blank"', $rendered);
    }

    public function test_note_show_view_uses_target_blank_for_share_link_and_reference(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $note = Note::create([
            'user_id' => $user->id,
            'title' => 'Hello',
            'slug' => 'hello',
            'body' => '<p>Lihat https://example.com/inline ya.</p>',
            'reference_url' => 'https://example.com/ref',
        ]);

        $html = $this->get(route('notes.show', $note))->getContent();

        // Share link is clickable & opens in new tab
        $this->assertMatchesRegularExpression(
            '#<a[^>]+href="[^"]+'.preg_quote((string) $note->id, '#').'-hello\.html"[^>]+target="_blank"#',
            $html,
            'Share link must open in a new window.'
        );

        // Reference URL opens in new tab
        $this->assertMatchesRegularExpression(
            '#<a[^>]+href="https://example\.com/ref"[^>]+target="_blank"#',
            $html
        );

        // Inline plain-text URL in body is auto-linked
        $this->assertStringContainsString('href="https://example.com/inline"', $html);
        $this->assertMatchesRegularExpression(
            '#<a[^>]+href="https://example\.com/inline"[^>]+target="_blank"#',
            $html
        );
    }
}
