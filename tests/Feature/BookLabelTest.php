<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookLabelTest extends TestCase
{
    use RefreshDatabase;

    public function test_label_route_returns_200_for_existing_book(): void
    {
        $book = $this->makeBook();

        $this->get("/books/{$book->id}/label")->assertStatus(200);
    }

    public function test_label_contains_book_data(): void
    {
        $book = $this->makeBook([
            'title' => 'The Hitchhiker\'s Guide',
            'dewey_classification' => '823.914',
        ], authorName: 'Douglas Adams');

        $response = $this->get("/books/{$book->id}/label");

        $response->assertStatus(200)
            ->assertSee('The Hitchhiker&#039;s Guide', false)
            ->assertSee('Douglas Adams')
            ->assertSee('823.914')
            ->assertSee('data-book-id="' . $book->id . '"', false);
    }

    public function test_label_uses_library_name_from_config(): void
    {
        config(['app.library_name' => 'Andrew\'s Library']);

        $book = $this->makeBook();

        $this->get("/books/{$book->id}/label")
            ->assertStatus(200)
            ->assertSee('Andrew&#039;s Library', false);
    }

    public function test_label_uses_domain_from_app_url(): void
    {
        config(['app.url' => 'https://library.example.com']);

        $book = $this->makeBook();

        $this->get("/books/{$book->id}/label")
            ->assertStatus(200)
            ->assertSee('library.example.com');
    }

    public function test_label_returns_404_for_missing_book(): void
    {
        $this->get('/books/999999/label')->assertStatus(404);
    }

    public function test_label_renders_without_dewey_classification(): void
    {
        $book = $this->makeBook(['dewey_classification' => null]);

        $this->get("/books/{$book->id}/label")
            ->assertStatus(200)
            ->assertDontSee('class="label__dewey"', false)
            ->assertSee('label__main no-dewey', false);
    }

    private function makeBook(array $attrs = [], string $authorName = 'Test Author'): Book
    {
        $author = Author::create(['name' => $authorName]);

        return Book::create(array_merge([
            'title' => 'Test Book',
            'author_id' => $author->id,
            'dewey_classification' => '000.00',
        ], $attrs));
    }
}
