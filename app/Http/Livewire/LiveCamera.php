<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiveCamera extends Component
{
    public bool $running = false;
    public int $faces = 0;
    public int $personsInCache = 0;

    /** Optional phone/camera URL (MJPEG or RTSP). Empty = local webcam. */
    public string $sourceUrl = '';

    /** Tracks currently visible in front of the camera (from AI engine). */
    public array $tracks = [];

    /** Recently seen people (keyed by national_code or track id). */
    public array $recent = [];

    public string $error = '';

    public function mount(): void
    {
        $this->refreshStatus();
    }

    public function refreshStatus(): void
    {
        $response = $this->aiRequest('GET', '/api/camera/status');

        if ($response === null) {
            $this->running = false;
            return;
        }

        $this->running = (bool) ($response['running'] ?? false);
        $this->faces = (int) ($response['faces'] ?? 0);
        $this->personsInCache = (int) ($response['persons_in_cache'] ?? 0);
    }

    public function startCamera(): void
    {
        $this->error = '';

        $sourceUrl = trim($this->sourceUrl) ?: null;

        $response = $this->aiRequest('POST', '/api/camera/start', [
            'camera_id' => 0,
            'source_url' => $sourceUrl,
        ]);

        if ($response === null) {
            $this->error = $sourceUrl
                ? 'اتصال به موتور هوش مصنوعی برقرار نشد یا دوربین گوشی در دسترس نیست. آدرس و اپ دوربین را بررسی کنید.'
                : 'اتصال به موتور هوش مصنوعی برقرار نشد. آیا سرویس در پورت 8003 اجرا می‌شود؟';
            return;
        }

        if (isset($response['detail'])) {
            $this->error = $response['detail'];
            return;
        }

        $this->running = true;
        $this->personsInCache = (int) ($response['persons_loaded'] ?? 0);
    }

    public function stopCamera(): void
    {
        $this->aiRequest('POST', '/api/camera/stop');
        $this->running = false;
        $this->tracks = [];
        $this->faces = 0;
    }

    /**
     * Poll the AI engine for active tracks.
     * Called from the Blade view with wire:poll.
     */
    public function pollTracks(): void
    {
        // Single engine call per poll: /api/tracks already carries `running`
        // and `count`, so the separate /api/camera/status round-trip is only
        // needed when the engine looks unreachable (recovery path).
        $response = $this->aiRequest('GET', '/api/tracks');

        if ($response === null) {
            $this->refreshStatus();
            $this->running = false;
            $this->tracks = [];
            $this->faces = 0;
            return;
        }

        $this->running = (bool) ($response['running'] ?? false);
        $this->faces = (int) ($response['count'] ?? 0);
        $this->tracks = $response['tracks'] ?? [];

        if (! $this->running) {
            $this->tracks = [];
            return;
        }

        // Merge recognized people into the recent list (newest first)
        foreach ($this->tracks as $track) {
            $key = $track['national_code'] ?? ('track-' . $track['track_id']);
            if (! empty($track['full_name'])) {
                // Prepend so the newest detection appears on top
                $entry = $track;
                $entry['seen_at'] = now()->format('H:i:s');
                unset($this->recent[$key]);
                $this->recent = [$key => $entry] + $this->recent;
            }
        }

        // Keep the recent list bounded
        $this->recent = array_slice($this->recent, 0, 20, true);
    }

    public function render()
    {
        return view('livewire.live-camera');
    }

    /**
     * Thin HTTP client for the AI engine with the shared API key.
     * Returns decoded JSON or null on connection failure.
     */
    private function aiRequest(string $method, string $path, array $data = []): ?array
    {
        try {
            $request = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.ai.api_key'),
                'Accept' => 'application/json',
            ])->timeout(3)->connectTimeout(1.5);

            $url = rtrim(config('services.ai.url'), '/') . $path;

            $response = $method === 'GET'
                ? $request->get($url)
                : $request->post($url, $data);

            if ($response->status() === 401) {
                Log::error('AI Engine auth failed - check AI_ENGINE_API_KEY');
                $this->error = 'خطای احراز هویت موتور هوش مصنوعی (API Key)';
                return null;
            }

            if (! $response->successful()) {
                Log::warning('AI Engine request failed', [
                    'path' => $path,
                    'status' => $response->status(),
                ]);
                // Still return body so callers can read 'detail' (e.g. camera busy)
                return $response->json() ?? ['detail' => 'HTTP ' . $response->status()];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('AI Engine connection error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
