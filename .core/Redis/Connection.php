<?php

// Connection.php

class RedisConnection {

    private static $instance = null;
    private $redis;

    private function __construct() {
        try {
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1', 6379);
        } catch (Exception $e) {
            throw new Exception("Redis connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->redis;
    }
    
}