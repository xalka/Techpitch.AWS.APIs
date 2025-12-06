<?php

class RedisService
{
    private $redis;

    public function __construct()
    {
        $this->connect();
    }

    /**
     * Establishes a Redis connection
     */
    private function connect()
    {
        try {
            $this->redis = new \Redis();
            $this->redis->connect(REDIS_HOST, REDIS_PORT);
        } catch (\Exception $e) {
            $this->logError("Redis connection failed: " . $e->getMessage());
            throw new \Exception("Redis connection failed: " . $e->getMessage());
        }
    }

    /**
     * Publish data to a channel
     * @param string $channel
     * @param mixed $data
     * @return int
     */
    public function publish(string $channel, $data): int
    {
        $payload = is_array($data) ? json_encode($data) : $data;

        return $this->redis->publish($channel, $payload);
    }

    /**
     * Check if a key exists in Redis
     * @param string $key
     * @return bool
     */
    public function keyExists(string $key): bool
    {
        return (bool)$this->redis->exists($key);
    }

    /**
     * Set a value with an optional expiration time
     * @param string $key
     * @param mixed $value
     * @param int|null $expire Time in seconds
     * @return bool
     */
    public function setValue(string $key, $value, int $expire = null): bool
    {
        $payload = is_array($value) ? json_encode($value) : $value;

        $result = $this->redis->set($key, $payload);

        if ($expire) {
            $this->redis->expire($key, $expire);
        }

        $this->logAction("SET", $key, $payload);
        return $result;
    }

    /**
     * Get the value of a key
     * @param string $key
     * @return string|null
     */
    public function getValue(string $key): ?string
    {
        return $this->redis->get($key) ?: null;
    }

    /**
     * Close the Redis connection
     */
    public function close(): void
    {
        $this->redis->close();
    }

    /**
     * Logs an error message
     * @param string $message
     */
    private function logError(string $message): void
    {
        $this->writeToFile(LOG_FILE_REDIS, "ERROR: " . $message);
    }

    /**
     * Logs an action for tracking purposes
     * @param string $action
     * @param string $key
     * @param mixed $value
     */
    private function logAction(string $action, string $key, $value): void
    {
        $this->writeToFile(LOG_FILE_REDIS, "$action: $key : $value");
    }

    /**
     * Write logs to a file
     * @param string $file
     * @param string $message
     */
    private function writeToFile(string $file, string $message): void
    {
        file_put_contents($file, "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL, FILE_APPEND);
    }
}
