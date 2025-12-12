<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\BookingModel;
use App\Models\MovieModel;
use App\Models\SeatHoldModel;

class BookingService
{
    public const HOLD_TTL_SECONDS = 300;

    private MovieModel $movies;
    private SeatHoldModel $holds;
    private BookingModel $bookings;
    private AuthService $auth;

    public function __construct(
        MovieModel $movies,
        SeatHoldModel $holds,
        BookingModel $bookings,
        AuthService $auth
    ) {
        $this->movies = $movies;
        $this->holds = $holds;
        $this->bookings = $bookings;
        $this->auth = $auth;
    }

    public function seatTemplate(): array
    {
        $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
        $cols = range(1, 10);
        $seats = [];
        foreach ($rows as $row) {
            foreach ($cols as $col) {
                $seats[] = $row . $col;
            }
        }
        return $seats;
    }

    public function cleanupExpiredHolds(array $holds, int $now): array
    {
        return $this->holds->cleanupExpired($holds, $now);
    }

    public function seatMap(string $showId, string $sessionId): array
    {
        $now = time();
        $holds = $this->cleanupExpiredHolds($this->holds->all(), $now);
        $this->holds->saveAll($holds);

        $bookings = $this->bookings->all();
        $bookedSeats = [];
        foreach ($bookings as $b) {
            if (($b['showId'] ?? null) === $showId) {
                foreach ($b['seats'] as $s) {
                    $bookedSeats[$s] = true;
                }
            }
        }

        $holdsForShow = array_filter($holds, fn($h) => ($h['showId'] ?? '') === $showId);
        $seatStatus = [];
        foreach ($this->seatTemplate() as $seat) {
            if (isset($bookedSeats[$seat])) {
                $seatStatus[] = ['code' => $seat, 'status' => 'booked'];
                continue;
            }
            $status = 'available';
            foreach ($holdsForShow as $hold) {
                if (in_array($seat, $hold['seats'], true)) {
                    $status = $hold['session'] === $sessionId ? 'held-you' : 'held';
                    break;
                }
            }
            $seatStatus[] = ['code' => $seat, 'status' => $status];
        }

        return [
            'seats' => $seatStatus,
            'holdTtlSeconds' => self::HOLD_TTL_SECONDS,
            'message' => 'Ghế giữ tạm trong 5 phút',
        ];
    }

    public function holdSeats(string $showId, array $seats, string $sessionId): array
    {
        $validSeats = $this->seatTemplate();
        foreach ($seats as $seat) {
            if (!in_array($seat, $validSeats, true)) {
                throw new \InvalidArgumentException("Ghế $seat không hợp lệ", 422);
            }
        }

        $now = time();
        $holds = $this->cleanupExpiredHolds($this->holds->all(), $now);
        $bookings = $this->bookings->all();

        foreach ($bookings as $b) {
            if (($b['showId'] ?? null) === $showId) {
                foreach ($b['seats'] as $s) {
                    if (in_array($s, $seats, true)) {
                        throw new \RuntimeException("Ghế $s đã được đặt", 409);
                    }
                }
            }
        }

        foreach ($holds as $hold) {
            if (($hold['showId'] ?? '') !== $showId) {
                continue;
            }
            if ($hold['session'] !== $sessionId) {
                foreach ($hold['seats'] as $s) {
                    if (in_array($s, $seats, true)) {
                        throw new \RuntimeException("Ghế $s đang được giữ bởi phiên khác", 409);
                    }
                }
            }
        }

        $holds = array_values(array_filter($holds, function ($h) use ($showId, $sessionId) {
            return !($h['showId'] === $showId && $h['session'] === $sessionId);
        }));

        $holds[] = [
            'session' => $sessionId,
            'showId' => $showId,
            'seats' => array_values(array_unique($seats)),
            'expiresAt' => $now + self::HOLD_TTL_SECONDS,
        ];

        $this->holds->saveAll($holds);

        return [
            'message' => 'Đã giữ ghế trong 5 phút',
            'expiresAt' => $now + self::HOLD_TTL_SECONDS,
        ];
    }

    public function releaseHold(string $showId, string $sessionId): int
    {
        $holds = $this->holds->all();
        $before = count($holds);
        $holds = array_values(array_filter($holds, function ($h) use ($showId, $sessionId) {
            return !($h['showId'] === $showId && $h['session'] === $sessionId);
        }));
        $this->holds->saveAll($holds);
        return $before - count($holds);
    }

    public function confirmBooking(string $showId, string $sessionId, ?string $username): array
    {
        $now = time();
        $holds = $this->cleanupExpiredHolds($this->holds->all(), $now);
        $this->holds->saveAll($holds);

        $myHold = null;
        foreach ($holds as $h) {
            if (($h['showId'] ?? '') === $showId && ($h['session'] ?? '') === $sessionId) {
                $myHold = $h;
                break;
            }
        }
        if (!$myHold || empty($myHold['seats'])) {
            throw new \RuntimeException('Bạn chưa giữ ghế nào cho suất này', 409);
        }

        $bookings = $this->bookings->all();
        foreach ($bookings as $b) {
            if (($b['showId'] ?? null) === $showId) {
                foreach ($b['seats'] as $seat) {
                    if (in_array($seat, $myHold['seats'], true)) {
                        throw new \RuntimeException("Ghế $seat vừa được đặt bởi người khác", 409);
                    }
                }
            }
        }

        $pricePerSeat = $this->priceForShow($showId);
        $totalPrice = $pricePerSeat * count($myHold['seats']);

        $booking = [
            'showId' => $showId,
            'seats' => $myHold['seats'],
            'session' => $sessionId,
            'bookedAt' => date('c', $now),
            'pricePerSeat' => $pricePerSeat,
            'totalPrice' => $totalPrice,
            'paid' => true,
            'user' => $username,
        ];
        $bookings[] = $booking;
        $this->bookings->saveAll($bookings);

        $this->releaseHold($showId, $sessionId);

        return [
            'message' => 'Đặt vé thành công',
            'booking' => $booking,
            'totalPrice' => $totalPrice,
        ];
    }

    public function myBookings(?string $username, string $sessionId): array
    {
        $bookings = $this->bookings->all();
        $movies = $this->movies->all();

        $movieMap = [];
        foreach ($movies as $m) {
            foreach ($m['showtimes'] as $st) {
                $movieMap[$st['id']] = ['title' => $m['title'], 'time' => $st['time'], 'movieId' => $m['id']];
            }
        }

        $result = [];
        foreach ($bookings as $b) {
            $isOwner = false;
            if ($username && ($b['user'] ?? null) === $username) {
                $isOwner = true;
            } elseif (!$username && ($b['session'] ?? '') === $sessionId) {
                $isOwner = true;
            }
            if ($isOwner) {
                $info = $movieMap[$b['showId']] ?? ['title' => $b['showId'], 'time' => '', 'movieId' => null];
                $result[] = [
                    'movieTitle' => $info['title'],
                    'showTime' => $info['time'],
                    'movieId' => $info['movieId'],
                    'showId' => $b['showId'],
                    'seats' => $b['seats'],
                    'totalPrice' => $b['totalPrice'] ?? 0,
                    'bookedAt' => $b['bookedAt'] ?? '',
                ];
            }
        }
        return $result;
    }

    public function cancelMyBooking(string $showId, array $seats, ?string $username, string $sessionId): bool
    {
        $bookings = $this->bookings->all();
        $changed = false;
        foreach ($bookings as $idx => $b) {
            if (($b['showId'] ?? '') !== $showId) {
                continue;
            }
            $isOwner = false;
            if ($username && ($b['user'] ?? null) === $username) {
                $isOwner = true;
            } elseif (!$username && ($b['session'] ?? null) === $sessionId) {
                $isOwner = true;
            }
            if (!$isOwner) {
                continue;
            }

            $remaining = array_values(array_diff($b['seats'], $seats));
            if (count($remaining) === count($b['seats'])) {
                continue;
            }
            if (empty($remaining)) {
                unset($bookings[$idx]);
            } else {
                $bookings[$idx]['seats'] = $remaining;
                $bookings[$idx]['totalPrice'] = ($b['pricePerSeat'] ?? 0) * count($remaining);
            }
            $changed = true;
        }

        if ($changed) {
            $bookings = array_values($bookings);
            $this->bookings->saveAll($bookings);
            $this->trimHoldsAfterCancel($showId, $seats);
        }

        return $changed;
    }

    public function adminCancel(string $showId, array $seats, ?string $username, ?string $session): bool
    {
        $bookings = $this->bookings->all();
        $changed = false;
        foreach ($bookings as $idx => $b) {
            if (($b['showId'] ?? '') !== $showId) {
                continue;
            }
            if ($username && ($b['user'] ?? null) !== $username) {
                continue;
            }
            if (!$username && $session && ($b['session'] ?? null) !== $session) {
                continue;
            }
            $remaining = array_values(array_diff($b['seats'], $seats));
            if (count($remaining) === count($b['seats'])) {
                continue;
            }
            if (empty($remaining)) {
                unset($bookings[$idx]);
            } else {
                $bookings[$idx]['seats'] = $remaining;
                $bookings[$idx]['totalPrice'] = ($b['pricePerSeat'] ?? 0) * count($remaining);
            }
            $changed = true;
        }
        if ($changed) {
            $bookings = array_values($bookings);
            $this->bookings->saveAll($bookings);
            $this->trimHoldsAfterCancel($showId, $seats);
        }
        return $changed;
    }

    public function resetAll(): void
    {
        $this->bookings->saveAll([]);
        $this->holds->saveAll([]);
    }

    public function saveMovie(array $input): array
    {
        $title = trim($input['title'] ?? '');
        $duration = (int)($input['duration'] ?? 0);
        $price = (int)($input['price'] ?? 0);
        $poster = trim($input['poster'] ?? '');
        $trailer = trim($input['trailer'] ?? '');
        $description = trim($input['description'] ?? '');
        $showtimesRaw = $input['showtimes'] ?? [];

        if ($title === '' || $duration <= 0 || $price < 0 || $poster === '' || $description === '' || empty($showtimesRaw)) {
            throw new \RuntimeException('Thiếu thông tin bắt buộc', 422);
        }

        $movies = $this->movies->all();
        $id = $input['id'] ?: 'mv-' . substr(uniqid(), -6);

        $showtimes = [];
        foreach ($showtimesRaw as $idx => $time) {
            $showtimes[] = [
                'id' => $id . '-' . ($idx + 1),
                'time' => $time,
            ];
        }

        $found = false;
        foreach ($movies as &$movie) {
            if (($movie['id'] ?? '') === $id) {
                $movie = [
                    'id' => $id,
                    'title' => $title,
                    'duration' => $duration,
                    'price' => $price,
                    'poster' => $poster,
                    'trailer' => $trailer,
                    'description' => $description,
                    'showtimes' => $showtimes,
                ];
                $found = true;
                break;
            }
        }
        unset($movie);

        if (!$found) {
            $movies[] = [
                'id' => $id,
                'title' => $title,
                'duration' => $duration,
                'price' => $price,
                'poster' => $poster,
                'trailer' => $trailer,
                'description' => $description,
                'showtimes' => $showtimes,
            ];
        }

        $this->movies->saveAll($movies);

        return ['message' => 'Đã lưu phim', 'id' => $id];
    }

    public function deleteMovie(string $id): bool
    {
        $movies = $this->movies->all();
        $movies = array_values(array_filter($movies, fn($m) => ($m['id'] ?? '') !== $id));
        return $this->movies->saveAll($movies);
    }

    private function priceForShow(string $showId): int
    {
        $info = $this->movies->findShowtime($showId);
        if (!$info) {
            return 0;
        }
        return (int)($info['movie']['price'] ?? 0);
    }

    private function trimHoldsAfterCancel(string $showId, array $seats): void
    {
        $holds = $this->holds->all();
        $holds = array_values(array_filter($holds, function ($h) use ($showId, $seats) {
            if (($h['showId'] ?? '') !== $showId) {
                return true;
            }
            $newSeats = array_diff($h['seats'] ?? [], $seats);
            $h['seats'] = $newSeats;
            return !empty($newSeats);
        }));
        $this->holds->saveAll($holds);
    }
}

