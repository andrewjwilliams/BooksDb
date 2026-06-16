<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;
use App\Models\BookEbook;

class FetchEbooks extends Command
{
    protected $signature   = 'books:fetch-ebooks {--force : Re-fetch even if ebook entries already exist}';
    protected $description = 'Fetch ebook links from Open Library for all books and store them in book_ebooks';

    public function handle(): int
    {
        $query = Book::whereNotNull('isbn');

        if (!$this->option('force')) {
            $booksWithEbooks = BookEbook::distinct()->pluck('book_id');
            $query->whereNotIn('id', $booksWithEbooks);
        }

        $books = $query->get();

        if ($books->isEmpty()) {
            $this->info('No books to process.');
            return 0;
        }

        $this->info("Processing {$books->count()} book(s)…");
        $bar = $this->output->createProgressBar($books->count());
        $bar->start();

        $found = 0;

        foreach ($books as $book) {
            $ebooks = $this->fetchEbooks($book);

            if (!empty($ebooks)) {
                if ($this->option('force')) {
                    $book->ebooks()->delete();
                }
                foreach ($ebooks as $ebook) {
                    $book->ebooks()->create($ebook);
                }
                $found++;
            }

            $bar->advance();
            // Be polite to the Open Library API
            usleep(500000);
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Found ebooks for {$found} book(s).");

        return 0;
    }

    private function fetchEbooks(Book $book): array
    {
        $isbn = $book->isbn_13 ?? $book->isbn ?? $book->isbn_10;

        $bibkey = null;
        if ($isbn) {
            $bibkey = 'ISBN:' . preg_replace('/[^0-9Xx]/', '', $isbn);
        } elseif ($book->openlibrary) {
            $bibkey = 'OLID:' . $book->openlibrary;
        }

        if (!$bibkey) {
            return [];
        }

        $url = 'https://openlibrary.org/api/books.json?bibkeys=' . urlencode($bibkey) . '&jscmd=data';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'booksdb/1.0');
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$body || $status !== 200) {
            return [];
        }

        $data = json_decode($body, true);
        if (!$data) {
            return [];
        }

        $entry = $data[$bibkey] ?? null;
        if (!$entry || empty($entry['ebooks'])) {
            return [];
        }

        $result = [];
        foreach ($entry['ebooks'] as $ebook) {
            $ebookUrl = $ebook['read_url'] ?? $ebook['preview_url'] ?? null;
            if (!$ebookUrl) continue;

            $siteName = 'Unknown';
            if (str_contains($ebookUrl, 'archive.org')) $siteName = 'Internet Archive';
            elseif (str_contains($ebookUrl, 'openlibrary.org')) $siteName = 'Open Library';

            $result[] = ['url' => $ebookUrl, 'site_name' => $siteName];
        }

        return $result;
    }
}
