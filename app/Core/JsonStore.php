<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Simple JSON file storage with locking to avoid race conditions.
 */
class JsonStore
{
    private string $dataPath;

    public function __construct(string $dataPath)
    {
        $this->dataPath = rtrim($dataPath, '/');
    }

    public function path(string $basename): string
    {
        return $this->dataPath . '/' . ltrim($basename, '/');
    }

    /**
        * Read JSON file, returning $default when file missing or invalid.
        */
    public function read(string $file, $default = [])
    {
        if (!file_exists($file)) {
            return $default;
        }
        $content = file_get_contents($file);
        if ($content === false || $content === '') {
            return $default;
        }
        $data = json_decode($content, true);
        return $data ?? $default;
    }

    /**
     * Write JSON with locking to prevent concurrent writes.
     */
    public function write(string $file, $data): bool
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $fp = fopen($file, 'c+');
        if (!$fp) {
            return false;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return false;
        }
        ftruncate($fp, 0);
        rewind($fp);
        $result = fwrite($fp, $json) !== false;
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return $result;
    }
}

