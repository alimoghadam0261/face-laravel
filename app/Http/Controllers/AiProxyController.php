<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Transparent proxy: browser /api/*  ->  AI engine /api/*.
 *
 * Why: on the HTTPS site (faceapp.ariyabodmenu.ir) the browser cannot reach
 * the engine directly (no public port, mixed content), and Laravel has no
 * /api/* routes of its own — so every fetch from mobile.html and every
 * <img src="/api/stream"> hit Laravel and 404'd in a loop, and no camera
 * frames ever reached the engine. This controller forwards the byte stream
 * both ways so push-mode frames, track polls and the MJPEG stream all work.
 */
class AiProxyController extends Controller
{
    private function engineUrl(string $path, Request $request): string
    {
        // The engine mounts its router with the /api prefix
        // (APIRouter(prefix="/api")), and $path carries the part after it.
        // Query string (e.g. ?session_id=...) must be preserved: per-device
        // session isolation depends on it.
        $qs = $request->getQueryString();
        return rtrim(config('services.ai.url'), '/') . '/api/' . ltrim($path, '/')
            . ($qs ? '?' . $qs : '');
    }

    private function engineHeaders(Request $request): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . config('services.ai.api_key'),
            'Accept' => $request->header('Accept', 'application/json'),
        ];
        if ($request->hasHeader('Content-Type')) {
            $headers['Content-Type'] = $request->header('Content-Type');
        }
        return $headers;
    }

    public function forward(Request $request, string $path)
    {
        $timeout = $path === 'stream' ? 0 : 15;   // MJPEG streams stay open

        if ($request->isMethod('POST')) {
            // Raw body passthrough: /api/frame carries a raw JPEG body, and
            // $request->getContent() preserves it byte-for-byte (Laravel does
            // not parse it because its Content-Type is image/jpeg).
            $response = Http::withHeaders($this->engineHeaders($request))
                ->withOptions(['stream' => true, 'timeout' => $timeout, 'connect_timeout' => 5])
                ->send('POST', $this->engineUrl($path, $request), ['body' => $request->getContent()]);
        } else {
            $response = Http::withHeaders($this->engineHeaders($request))
                ->withOptions(['stream' => true, 'timeout' => $timeout, 'connect_timeout' => 5])
                ->send('GET', $this->engineUrl($path, $request));
        }

        $status = $response->status();
        $contentType = $response->header('Content-Type');

        // JSON errors / small payloads: buffer and return as-is
        if ($path !== 'stream' || $status !== 200) {
            $body = $response->toPsrResponse()->getBody()->getContents();
            return new Response($body, $status, ['Content-Type' => $contentType ?: 'application/json']);
        }

        // MJPEG stream: pipe bytes to the client without buffering.
        $body = $response->toPsrResponse()->getBody();
        return new StreamedResponse(function () use ($body) {
            while (!$body->eof()) {
                echo $body->read(8192);
                if (connection_aborted()) {
                    $body->close();
                    break;
                }
                flush();
            }
        }, 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-store',
            'X-Accel-Buffering' => 'no',   // disable nginx proxy buffering
        ]);
    }

    /** POST /api/frame — raw JPEG from the phone/laptop browser (hot path). */
    public function frame(Request $request)
    {
        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.ai.api_key'),
                'Content-Type' => 'application/octet-stream',
            ])
            ->timeout(10)
            ->withBody($request->getContent(), 'application/octet-stream')
            ->post($this->engineUrl('frame', $request));

        return new Response(
            (string) $response->body(),
            $response->status(),
            ['Content-Type' => 'application/json']
        );
    }
}
