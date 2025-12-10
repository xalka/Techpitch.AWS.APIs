<?php

class RedisHelper {

    private Redis $redis;
    private bool $connected = false;

    public function __construct()
    {
        $this->connect();
    }

    /**
     * Connect to Redis (persistent)
     */
    private function connect(): void
    {
        try {
            $this->redis = new Redis();

            // Persistent connection
            $this->redis->pconnect(REDIS_HOST, REDIS_PORT, 2.0);

            // Authenticate if password exists
            if (REDIS_PASS !== '') {
                if (!$this->redis->auth(REDIS_PASS)) {
                    throw new Exception("Redis authentication failed");
                }
            }

            // Select DB
            if (defined('REDIS_DB_INDEX')) {
                $this->redis->select(REDIS_DB_INDEX);
            }

            $this->connected = true;

        } catch (Throwable $e) {
            writeToFile(LOG_FILE_REDIS, "Redis connection failed: " . $e->getMessage());
            $this->connected = false;
        }
    }

    /**
     * Publish to a channel
     */
    public function publish(string $channel, $data): int
    {
        if (!$this->connected || !$channel) return 0;

        try {
            if (is_array($data)) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            }

            return $this->redis->publish($channel, $data);

        } catch (Throwable $e) {
            writeToFile(LOG_FILE_REDIS, "Publish Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if key exists
     */
    public function exists(string $key): bool
    {
        if (!$this->connected) return false;

        try {
            return $this->redis->exists($key) > 0;
        } catch (Throwable $e) {
            writeToFile(LOG_FILE_REDIS, "Exists Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set a value with optional TTL
     */
    public function set(string $key, string $value, ?int $expire = null): bool
    {
        if (!$this->connected) return false;

        try {
            $status = $this->redis->set($key, $value);

            if ($status && $expire) {
                $this->redis->expire($key, $expire*60);
            }

            return $status;

        } catch (Throwable $e) {
            writeToFile(LOG_FILE_REDIS, "Set Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a key value
     */
    public function get(string $key): ?string
    {
        if (!$this->connected) return null;

        try {
            $value = $this->redis->get($key);
            return $value !== false ? $value : null;

        } catch (Throwable $e) {
            writeToFile(LOG_FILE_REDIS, "Get Error: " . $e->getMessage());
            return null;
        }
    }
}
