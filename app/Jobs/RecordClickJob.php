<?php

namespace App\Jobs;

use App\Models\Click;
use App\Models\Url;
use App\Services\Analytics\ClickEventPublisher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RecordClickJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(private readonly array $payload)
    {
        $this->onQueue('clicks');
    }

    public function handle(ClickEventPublisher $publisher): void
    {
        DB::transaction(function (): void {
            Click::query()->create([
                'url_id' => $this->payload['url_id'],
                'ip_hash' => $this->payload['ip_hash'] ?? null,
                'user_agent_hash' => $this->payload['user_agent_hash'] ?? null,
                'referer' => $this->payload['referer'] ?? null,
                'country' => $this->payload['country'] ?? null,
            ]);

            Url::query()
                ->whereKey($this->payload['url_id'])
                ->update([
                    'clicks_count' => DB::raw('clicks_count + 1'),
                    'last_clicked_at' => now(),
                ]);
        });

        $publisher->publish($this->payload + ['recorded_at' => now()->toIso8601String()]);
    }
}
