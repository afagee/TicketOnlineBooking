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
        $movies = $this->store->read($this->file, []);
        return $this->adjustShowtimesToNow($movies);
    }

    /**
     * Điều chỉnh lịch chiếu để luôn bắt đầu từ hôm nay trở đi
     * Giữ nguyên pattern giờ chiếu, chỉ dịch ngày
     */
    private function adjustShowtimesToNow(array $movies): array
    {
        if (empty($movies)) {
            return $movies;
        }

        // Tìm ngày nhỏ nhất trong tất cả lịch chiếu
        $minDate = null;
        foreach ($movies as $movie) {
            foreach ($movie['showtimes'] ?? [] as $st) {
                $time = $st['time'] ?? '';
                if ($time) {
                    $date = substr($time, 0, 10); // YYYY-MM-DD
                    if ($minDate === null || $date < $minDate) {
                        $minDate = $date;
                    }
                }
            }
        }

        if (!$minDate) {
            return $movies;
        }

        // Tính số ngày cần dịch để minDate = hôm nay
        $minTimestamp = strtotime($minDate);
        $todayTimestamp = strtotime(date('Y-m-d'));
        $daysDiff = (int)(($todayTimestamp - $minTimestamp) / 86400);

        // Nếu lịch chiếu đã ở tương lai, không cần dịch
        if ($daysDiff <= 0) {
            return $movies;
        }

        // Dịch tất cả lịch chiếu
        foreach ($movies as &$movie) {
            foreach ($movie['showtimes'] ?? [] as &$st) {
                $time = $st['time'] ?? '';
                if ($time) {
                    $timestamp = strtotime($time);
                    $newTimestamp = $timestamp + ($daysDiff * 86400);
                    $st['time'] = date('Y-m-d H:i', $newTimestamp);
                }
            }
        }

        return $movies;
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

