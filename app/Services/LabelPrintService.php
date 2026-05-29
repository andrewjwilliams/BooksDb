<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LabelPrintService
{
    public function __construct(private LabelImageService $images) {}

    /**
     * Render the label and dispatch it to the printer via the sidecar.
     *
     * @return array{ok: bool, status: int, error?: string, detail?: string}
     */
    public function print(Book $book): array
    {
        if (! config('printing.host')) {
            return [
                'ok' => false,
                'status' => 422,
                'error' => 'Printer not configured (LABEL_PRINTER_HOST is unset)',
            ];
        }

        $tape = (string) config('printing.tape', '62');

        try {
            $png = $this->images->render($book, $tape);
        } catch (\Throwable $e) {
            Log::error('label render failed', ['book' => $book->id, 'error' => $e->getMessage()]);
            return [
                'ok' => false,
                'status' => 500,
                'error' => 'Label render failed',
                'detail' => $e->getMessage(),
            ];
        }

        $url = rtrim((string) config('printing.sidecar_url'), '/').'/print';

        try {
            $response = Http::timeout(15)->acceptJson()->post($url, [
                'png_b64' => base64_encode($png),
                'label' => $tape,
                'host' => config('printing.host'),
                'copies' => 1,
            ]);
        } catch (ConnectionException $e) {
            Log::warning('printer sidecar unreachable', ['url' => $url, 'error' => $e->getMessage()]);
            return [
                'ok' => false,
                'status' => 502,
                'error' => 'Printer service unavailable',
                'detail' => $e->getMessage(),
            ];
        }

        if (! $response->successful()) {
            $body = $response->json() ?: [];
            return [
                'ok' => false,
                'status' => $response->status() === 503 ? 422 : 502,
                'error' => $body['error'] ?? 'Printer error',
                'detail' => $body['detail'] ?? $response->body(),
            ];
        }

        return [
            'ok' => true,
            'status' => 200,
        ];
    }
}
