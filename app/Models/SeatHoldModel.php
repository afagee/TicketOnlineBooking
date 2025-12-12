<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\JsonStore;

class SeatHoldModel
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

    public function saveAll(array $holds): bool
    {
        return $this->store->write($this->file, $holds);
    }

    public function cleanupExpired(array $holds, int $now): array
    {
        return array_values(array_filter($holds, function ($hold) use ($now) {
            return isset($hold['expiresAt']) && $hold['expiresAt'] > $now;
        }));
    }
}

