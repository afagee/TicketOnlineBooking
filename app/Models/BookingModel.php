<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\JsonStore;

class BookingModel
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

    public function saveAll(array $bookings): bool
    {
        return $this->store->write($this->file, $bookings);
    }
}

