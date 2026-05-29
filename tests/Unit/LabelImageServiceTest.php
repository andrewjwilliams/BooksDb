<?php

namespace Tests\Unit;

use App\Models\Author;
use App\Models\Book;
use App\Services\LabelImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelImageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_render_returns_png_with_correct_dimensions(): void
    {
        $book = $this->makeBook(['title' => 'A Book', 'dewey_classification' => '823']);

        $png = (new LabelImageService())->render($book, '62');

        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $png, 'expected a PNG file signature');

        $info = getimagesizefromstring($png);
        $this->assertNotFalse($info, 'PNG could not be parsed');
        $this->assertSame(696, $info[0], 'width should be 696px for tape "62"');
        $this->assertSame(342, $info[1], 'height should be 342px for tape "62"');
    }

    public function test_render_is_deterministic_for_same_book(): void
    {
        $book = $this->makeBook(['title' => 'A Book']);
        $svc = new LabelImageService();

        $this->assertSame(
            md5($svc->render($book, '62')),
            md5($svc->render($book, '62')),
        );
    }

    public function test_render_throws_on_unknown_tape(): void
    {
        $book = $this->makeBook();

        $this->expectException(\InvalidArgumentException::class);
        (new LabelImageService())->render($book, 'nope');
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
