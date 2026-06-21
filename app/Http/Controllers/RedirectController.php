<?php

namespace App\Http\Controllers;

use App\Jobs\RecordClickJob;
use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RedirectController extends Controller
{
    public function __invoke(Request $request, string $code)
    {
        $url = $this->findUrl($code);

        abort_if($url === null || ! $url->isAvailable(), 404);

        rescue(fn () => RecordClickJob::dispatch([
            'url_id' => $url->id,
            'short_code' => $url->short_code,
            'target' => $url->original_url,
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip().config('app.key')) : null,
            'user_agent_hash' => $request->userAgent() ? hash('sha256', $request->userAgent()) : null,
            'referer' => $request->headers->get('referer'),
            'country' => null,
            'clicked_at' => now()->toIso8601String(),
        ]), report: false);

        return redirect()->away($url->original_url, 302);
    }

    private function findUrl(string $code): ?Url
    {
        return rescue(
            fn () => Cache::remember("url:code:{$code}", now()->addMinutes(10), fn () => $this->query($code)),
            fn () => $this->query($code),
            report: false
        );
    }

    private function query(string $code): ?Url
    {
        return Url::query()->where('short_code', $code)->first();
    }
}
