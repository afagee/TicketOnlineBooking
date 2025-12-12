<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\JsonStore;

class MovieModel
{
    private JsonStore $store;
    private string $file;

    public function __construct(JsonStore $store, string $file)
    {
        $this->store = $store;
        $this->file = $file;
    }

    public function all(): array
    {
        return $this->store->read($this->file, []);
    }

    public function saveAll(array $movies): bool
    {
        return $this->store->write($this->file, $movies);
    }

    public function find(string $id): ?array
    {
        foreach ($this->all() as $movie) {
            if (($movie['id'] ?? null) === $id) {
                return $movie;
            }
        }
        return null;
    }

    public function findShowtime(string $showId): ?array
    {
        foreach ($this->all() as $movie) {
            foreach ($movie['showtimes'] ?? [] as $st) {
                if (($st['id'] ?? '') === $showId) {
                    return [
                        'movie' => $movie,
                        'showtime' => $st,
                    ];
                }
            }
        }
        return null;
    }
}

