<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\BookingService;

class BookingApiController
{
    private BookingService $booking;

    public function __construct(BookingService $booking)
    {
        $this->booking = $booking;
    }

    public function seatMap(string $showId, string $sessionId): array
    {
        return $this->booking->seatMap($showId, $sessionId);
    }

    public function hold(string $showId, array $seats, string $sessionId): array
    {
        return $this->booking->holdSeats($showId, $seats, $sessionId);
    }

    public function release(string $showId, string $sessionId): int
    {
        return $this->booking->releaseHold($showId, $sessionId);
    }

    public function confirm(string $showId, string $sessionId, ?string $username): array
    {
        return $this->booking->confirmBooking($showId, $sessionId, $username);
    }

    public function myBookings(?string $username, string $sessionId): array
    {
        return $this->booking->myBookings($username, $sessionId);
    }

    public function cancelMine(string $showId, array $seats, ?string $username, string $sessionId): bool
    {
        return $this->booking->cancelMyBooking($showId, $seats, $username, $sessionId);
    }

    public function adminCancel(string $showId, array $seats, ?string $username, ?string $session): bool
    {
        return $this->booking->adminCancel($showId, $seats, $username, $session);
    }

    public function resetAll(): void
    {
        $this->booking->resetAll();
    }
}

