<?php

namespace Tests\Feature;

use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_short_url_can_be_created(): void
    {
        $response = $this->postJson('/api/urls', [
            'original_url' => 'https://example.com/docs/getting-started',
            'custom_code' => 'docs',
            'title' => 'Docs',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.short_code', 'docs')
            ->assertJsonPath('data.original_url', 'https://example.com/docs/getting-started');

        $this->assertDatabaseHas('urls', [
            'short_code' => 'docs',
            'clicks_count' => 0,
        ]);
    }

    public function test_auto_generated_short_url_uses_configured_domain_and_code_length(): void
    {
        config([
            'url-shortener.base_url' => 'https://short.example',
            'url-shortener.code_length' => 8,
        ]);

        $response = $this->postJson('/api/urls', [
            'original_url' => 'https://example.com/pricing',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.short_url', fn (string $shortUrl) => str_starts_with($shortUrl, 'https://short.example/'))
            ->assertJsonPath('data.short_code', fn (string $code) => strlen($code) === 8);
    }

    public function test_redirect_records_click_analytics(): void
    {
        $url = Url::query()->create([
            'original_url' => 'https://example.com/launch',
            'short_code' => 'launch',
            'title' => 'Launch',
        ]);

        $this->get('/launch')
            ->assertRedirect('https://example.com/launch');

        $this->assertDatabaseHas('clicks', [
            'url_id' => $url->id,
        ]);

        $this->assertSame(1, $url->refresh()->clicks_count);
    }

    public function test_paused_url_returns_not_found(): void
    {
        Url::query()->create([
            'original_url' => 'https://example.com/paused',
            'short_code' => 'paused',
            'is_active' => false,
        ]);

        $this->get('/paused')->assertNotFound();
    }
}
