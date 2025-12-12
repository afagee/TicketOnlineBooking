<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\BookingService;

class AdminApiController
{
    private BookingService $booking;

    public function __construct(BookingService $booking)
    {
        $this->booking = $booking;
    }

    public function saveMovie(array $input): array
    {
        return $this->booking->saveMovie($input);
    }

    public function deleteMovie(string $id): bool
    {
        return $this->booking->deleteMovie($id);
    }
}

