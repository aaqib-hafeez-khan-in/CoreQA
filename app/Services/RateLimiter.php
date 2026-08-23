<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class RateLimiter
{
    public function __construct(private PDO $db) {}

    public function hit(string $route, string $ip, int $max, int $windowSec): bool
    {
        $ipBin = inet_pton($ip) ?: hash('sha256', $ip, true);
        $windowStart = (int)(time() / $windowSec) * $windowSec;
        $window = date('Y-m-d H:i:s', $windowStart);
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        $this->db->beginTransaction();
        try {
            $select = $driver === 'sqlite'
                ? 'SELECT id, hits FROM rate_limits WHERE ip = ? AND route = ? AND window_start = ?'
                : 'SELECT id, hits FROM rate_limits WHERE ip = ? AND route = ? AND window_start = ? FOR UPDATE';
            $stmt = $this->db->prepare($select);
            $stmt->execute([$ipBin, $route, $window]);
            $row = $stmt->fetch();

            if ($row) {
                if ((int)$row['hits'] >= $max) {
                    $this->db->rollBack();
                    return false;
                }
                $this->db->prepare('UPDATE rate_limits SET hits = hits + 1 WHERE id = ?')->execute([$row['id']]);
            } else {
                $this->db->prepare('INSERT INTO rate_limits (ip, route, window_start, hits) VALUES (?, ?, ?, 1)')
                    ->execute([$ipBin, $route, $window]);
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
