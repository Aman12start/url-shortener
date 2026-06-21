<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Url;
use App\Services\ShortCodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class UrlController extends Controller
{
    public function index(): JsonResponse
    {
        $urls = Url::query()
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Url $url) => $this->serialize($url));

        return response()->json([
            'data' => $urls,
            'stats' => [
                'links' => Url::query()->count(),
                'clicks' => Url::query()->sum('clicks_count'),
            ],
        ]);
    }

    public function store(Request $request, ShortCodeGenerator $generator): JsonResponse
    {
        $validated = $request->validate([
            'original_url' => ['required', 'url:http,https', 'max:2048'],
            'custom_code' => [
                'nullable',
                'alpha_dash:ascii',
                'min:3',
                'max:20',
                Rule::unique('urls', 'short_code'),
            ],
            'title' => ['nullable', 'string', 'max:120'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $url = Url::query()->create([
            'original_url' => $validated['original_url'],
            'short_code' => $validated['custom_code'] ?? $generator->unique(),
            'title' => $validated['title'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        $this->forgetCode($url->short_code);

        return response()->json(['data' => $this->serialize($url)], 201);
    }

    public function show(Url $url): JsonResponse
    {
        $url->loadCount('clicks');

        return response()->json(['data' => $this->serialize($url)]);
    }

    public function update(Request $request, Url $url): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $url->update($validated);
        $this->forgetCode($url->short_code);

        return response()->json(['data' => $this->serialize($url->refresh())]);
    }

    public function destroy(Url $url): JsonResponse
    {
        $code = $url->short_code;
        $url->delete();
        $this->forgetCode($code);

        return response()->json(status: 204);
    }

    private function serialize(Url $url): array
    {
        return [
            'id' => $url->id,
            'original_url' => $url->original_url,
            'short_code' => $url->short_code,
            'short_url' => $url->short_url,
            'title' => $url->title,
            'clicks_count' => $url->clicks_count,
            'last_clicked_at' => $url->last_clicked_at?->toIso8601String(),
            'expires_at' => $url->expires_at?->toIso8601String(),
            'is_active' => $url->is_active,
            'created_at' => $url->created_at?->toIso8601String(),
        ];
    }

    private function forgetCode(string $code): void
    {
        rescue(fn () => Cache::forget("url:code:{$code}"), report: false);
    }
}
