<?php

/**
 * Create a Redis connection
 * - Uses persistent connection for speed (pconnect)
 * - Authenticates if password exists
 * - Selects DB automatically
 */
function redisConn(): ?Redis {
    try {
        $redis = new Redis();

        // Persistent connection = faster for repeated FPM requests
        $redis->pconnect(REDIS_HOST, REDIS_PORT, 2.0);

        // Authenticate only when password exists
        if (REDIS_PASS !== '') {
            if (!$redis->auth(REDIS_PASS)) {
                throw new Exception("Redis authentication failed");
            }
        }

        // Select DB if defined
        if (defined('REDIS_DB_INDEX')) {
            $redis->select(REDIS_DB_INDEX);
        }

        return $redis;

    } catch (Throwable $e) {
        writeToFile(LOG_FILE_REDIS, "Redis connection failed: " . $e->getMessage());
        return null;
    }
}


/**
 * Publish data to a Redis channel (PUB/SUB)
 */
function redisPub(?string $channel, $data): int
{
    if (!$channel) return 0;

    try {
        $redis = redisConn();
        if (!$redis) return 0;

        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        return $redis->publish($channel, $data);

    } catch (Throwable $e) {
        writeToFile(LOG_FILE_REDIS, "redisPub Error: " . $e->getMessage());
        return 0;
    }
}


/**
 * Check if a key exists
 */
function redisKeyExists(string $key): bool
{
    try {
        $redis = redisConn();
        if (!$redis) return false;

        return $redis->exists($key) > 0;

    } catch (Throwable $e) {
        writeToFile(LOG_FILE_REDIS, "redisKeyExists Error: " . $e->getMessage());
        return false;
    }
}


/**
 * Set a Redis key with optional expiration (TTL)
 */
function redisSetValue(string $key, string $value, ?int $expire = null): bool
{
    try {
        $redis = redisConn();
        if (!$redis) return false;

        $status = $redis->set($key, $value);

        if ($status && $expire) {
            $redis->expire($key, $expire);
        }

        return $status;

    } catch (Throwable $e) {
        writeToFile(LOG_FILE_REDIS, "redisSetValue Error: " . $e->getMessage());
        return false;
    }
}


/**
 * Get a key value
 */
function redisGetValue(string $key)
{
    try {
        $redis = redisConn();
        if (!$redis) return null;

        $value = $redis->get($key);

        // Return null instead of false for non-existing keys
        return $value !== false ? $value : null;

    } catch (Throwable $e) {
        writeToFile(LOG_FILE_REDIS, "redisGetValue Error: " . $e->getMessage());
        return null;
    }
}
