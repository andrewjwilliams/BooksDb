<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Author;

class FetchAuthorData extends Command
{
    protected $signature   = 'authors:fetch-ol-data {--force : Re-fetch even if data already exists}';
    protected $description = 'Fetch author metadata from Open Library for all authors with an OL ref';

    public function handle(): int
    {
        $query = Author::whereNotNull('open_library_ref');

        if (!$this->option('force')) {
            $query->whereNull('birth_date')
                  ->whereNull('bio')
                  ->whereNull('fuller_name');
        }

        $authors = $query->get();

        if ($authors->isEmpty()) {
            $this->info('No authors to process.');
            return 0;
        }

        $this->info("Processing {$authors->count()} author(s)…");
        $bar = $this->output->createProgressBar($authors->count());
        $bar->start();

        $updated = 0;

        foreach ($authors as $author) {
            $data = $this->fetchFromOl($author->open_library_ref);

            if ($data) {
                if (!empty($data['fuller_name']))  $author->fuller_name = $data['fuller_name'];
                if (!empty($data['birth_date']))   $author->birth_date  = $data['birth_date'];
                if (!empty($data['death_date']))   $author->death_date  = $data['death_date'];
                if (!empty($data['bio']))          $author->bio         = $data['bio'];
                if (!empty($data['remote_ids']))   $author->remote_ids  = $data['remote_ids'];

                $author->save();

                if (!empty($data['links'])) {
                    if ($this->option('force')) {
                        $author->links()->delete();
                    }
                    if ($author->links()->count() === 0) {
                        foreach ($data['links'] as $link) {
                            if (!empty($link['url'])) {
                                $author->links()->create([
                                    'title' => $link['title'] ?? $link['url'],
                                    'url'   => $link['url'],
                                ]);
                            }
                        }
                    }
                }

                $updated++;
            }

            $bar->advance();
            usleep(500000);
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Updated {$updated} author(s).");

        return 0;
    }

    private function fetchFromOl(string $olRef): ?array
    {
        $url = 'https://openlibrary.org/authors/' . urlencode($olRef) . '.json';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'booksdb/1.0');
        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$body || $status !== 200) {
            return null;
        }

        $data = json_decode($body, true);
        if (!$data) {
            return null;
        }

        $result = [];

        if (!empty($data['fuller_name']))  $result['fuller_name'] = $data['fuller_name'];
        elseif (!empty($data['personal_name'])) $result['fuller_name'] = $data['personal_name'];

        if (!empty($data['birth_date']))   $result['birth_date']  = $data['birth_date'];
        if (!empty($data['death_date']))   $result['death_date']  = $data['death_date'];

        if (!empty($data['bio'])) {
            $result['bio'] = is_string($data['bio']) ? $data['bio'] : ($data['bio']['value'] ?? '');
        }

        if (!empty($data['remote_ids']) && is_array($data['remote_ids'])) {
            $result['remote_ids'] = $data['remote_ids'];
        }

        if (!empty($data['links'])) {
            $result['links'] = array_values(array_filter(
                array_map(fn($l) => isset($l['url']) ? ['title' => $l['title'] ?? $l['url'], 'url' => $l['url']] : null, $data['links'])
            ));
        }

        return $result ?: null;
    }
}
