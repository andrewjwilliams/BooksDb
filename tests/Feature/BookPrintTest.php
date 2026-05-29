<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_print_returns_404_for_missing_book(): void
    {
        config(['printing.host' => '192.0.2.1']);
        Http::fake();

        $this->postJson('/books/999999/print')->assertStatus(404);
    }

    public function test_print_returns_422_when_host_not_configured(): void
    {
        config(['printing.host' => null]);
        Http::fake();

        $book = $this->makeBook();

        $this->postJson("/books/{$book->id}/print")
            ->assertStatus(422)
            ->assertJson([
                'ok' => false,
                'error' => 'Printer not configured (LABEL_PRINTER_HOST is unset)',
            ]);

        Http::assertNothingSent();
    }

    public function test_print_dispatches_png_and_label_to_sidecar(): void
    {
        config([
            'printing.host' => '192.0.2.1',
            'printing.tape' => '62',
            'printing.sidecar_url' => 'http://127.0.0.1:5151',
        ]);

        Http::fake([
            '127.0.0.1:5151/*' => Http::response(['ok' => true, 'bytes_sent' => 1234]),
        ]);

        $book = $this->makeBook();

        $response = $this->postJson("/books/{$book->id}/print");

        $response->assertStatus(200)->assertJson(['ok' => true]);

        Http::assertSent(function ($request) {
            $body = $request->data();
            return $request->url() === 'http://127.0.0.1:5151/print'
                && isset($body['png_b64'], $body['label'], $body['host'])
                && $body['label'] === '62'
                && $body['host'] === '192.0.2.1'
                && str_starts_with(base64_decode($body['png_b64']), "\x89PNG");
        });
    }

    public function test_print_returns_502_when_sidecar_returns_error(): void
    {
        config(['printing.host' => '192.0.2.1']);

        Http::fake([
            '127.0.0.1:5151/*' => Http::response(
                ['ok' => false, 'error' => 'Printer connection refused'],
                502
            ),
        ]);

        $book = $this->makeBook();

        $this->postJson("/books/{$book->id}/print")
            ->assertStatus(502)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('error', 'Printer connection refused');
    }

    public function test_print_returns_502_when_sidecar_unreachable(): void
    {
        config(['printing.host' => '192.0.2.1']);

        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $book = $this->makeBook();

        $this->postJson("/books/{$book->id}/print")
            ->assertStatus(502)
            ->assertJsonPath('ok', false);
    }

    private function makeBook(array $attrs = []): Book
    {
        $author = Author::create(['name' => 'Test Author']);
        return Book::create(array_merge([
            'title' => 'Test Book',
            'author_id' => $author->id,
        ], $attrs));
    }
}
