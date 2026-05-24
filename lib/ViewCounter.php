<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class ViewCounter {
    private const KEY_PREFIX = 'xforum:view_counter:';
    private const COUNTS_KEY = 'counts';
    private const LAST_FLUSH_KEY = 'last_flush';
    private const LOCK_KEY = 'lock';
    private const FLUSH_INTERVAL = 300;
    private const APCU_TTL = 0;
    private const LOCK_TTL = 10;

    public static function increment(int $tid): bool {
        if ($tid <= 0) {
            return true;
        }

        if (!self::isApcuAvailable()) {
            return false;
        }

        $stored = self::withLock(function () use ($tid): void {
            $counts = self::getCounts();
            $counts[$tid] = ($counts[$tid] ?? 0) + 1;
            apcu_store(self::key(self::COUNTS_KEY), $counts, self::APCU_TTL);
        });
        if (!$stored) {
            return false;
        }

        self::flushIfDue();
        return true;
    }

    public static function getPending(int $tid): int {
        if ($tid <= 0 || !self::isApcuAvailable()) {
            return 0;
        }

        $counts = self::getCounts();
        return (int)($counts[$tid] ?? 0);
    }

    public static function applyPendingToThread(?array $thread): ?array {
        if (!$thread || !isset($thread['tid'])) {
            return $thread;
        }

        $thread['view_num'] = (int)($thread['view_num'] ?? 0) + self::getPending((int)$thread['tid']);
        return $thread;
    }

    public static function applyPendingToThreads(array $threads): array {
        if (empty($threads) || !self::isApcuAvailable()) {
            return $threads;
        }

        $counts = self::getCounts();
        if (empty($counts)) {
            return $threads;
        }

        foreach ($threads as &$thread) {
            $tid = (int)($thread['tid'] ?? 0);
            if ($tid > 0 && isset($counts[$tid])) {
                $thread['view_num'] = (int)($thread['view_num'] ?? 0) + (int)$counts[$tid];
            }
        }
        unset($thread);

        return $threads;
    }

    public static function flushIfDue(): void {
        if (!self::isApcuAvailable()) {
            return;
        }

        $lastFlush = (int)(apcu_fetch(self::key(self::LAST_FLUSH_KEY)) ?: 0);
        if ($lastFlush <= 0) {
            apcu_store(self::key(self::LAST_FLUSH_KEY), time(), self::APCU_TTL);
            return;
        }

        if ($lastFlush > 0 && time() - $lastFlush < self::FLUSH_INTERVAL) {
            return;
        }

        self::flush();
    }

    public static function flush(): void {
        if (!self::isApcuAvailable()) {
            return;
        }

        self::withLock(function (): void {
            $counts = self::getCounts();
            if (empty($counts)) {
                apcu_store(self::key(self::LAST_FLUSH_KEY), time(), self::APCU_TTL);
                return;
            }

            $remaining = $counts;

            try {
                foreach ($counts as $tid => $num) {
                    $tid = (int)$tid;
                    $num = (int)$num;
                    if ($tid <= 0 || $num <= 0) {
                        unset($remaining[$tid]);
                        continue;
                    }

                    Database::query(
                        "UPDATE next_thread SET view_num = view_num + :num WHERE tid = :tid",
                        ['num' => $num, 'tid' => $tid]
                    );

                    unset($remaining[$tid]);
                    if (empty($remaining)) {
                        apcu_delete(self::key(self::COUNTS_KEY));
                    } else {
                        apcu_store(self::key(self::COUNTS_KEY), $remaining, self::APCU_TTL);
                    }
                }

                apcu_store(self::key(self::LAST_FLUSH_KEY), time(), self::APCU_TTL);
            } catch (\Throwable $e) {
                if (!empty($remaining)) {
                    apcu_store(self::key(self::COUNTS_KEY), $remaining, self::APCU_TTL);
                }
            }
        });
    }

    private static function getCounts(): array {
        $success = false;
        $counts = apcu_fetch(self::key(self::COUNTS_KEY), $success);

        if (!$success || !is_array($counts)) {
            return [];
        }

        return $counts;
    }

    private static function withLock(callable $callback): bool {
        if (!function_exists('apcu_add')) {
            $callback();
            return true;
        }

        $lockKey = self::key(self::LOCK_KEY);
        $deadline = microtime(true) + 1.0;

        while (!apcu_add($lockKey, 1, self::LOCK_TTL)) {
            if (microtime(true) >= $deadline) {
                return false;
            }
            usleep(10000);
        }

        try {
            $callback();
            return true;
        } finally {
            apcu_delete($lockKey);
        }
    }

    private static function key(string $name): string {
        return self::KEY_PREFIX . md5(ROOT_PATH) . ':' . $name;
    }

    private static function isApcuAvailable(): bool {
        if (!function_exists('apcu_fetch') || !function_exists('apcu_store') || !function_exists('apcu_delete')) {
            return false;
        }

        if (function_exists('apcu_enabled')) {
            return apcu_enabled();
        }

        return filter_var((string)ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN);
    }
}
?>
