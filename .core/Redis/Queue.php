<?php

// AdvancedQueue.php

class AdvancedQueue {
    
    private $redis;
    private $queueName;
    private $processingQueueName;
    private $deadLetterQueueName;

    public function __construct($queueName = 'default_queue') {
        $this->redis = RedisConnection::getInstance()->getConnection();
        $this->queueName = $queueName;
        $this->processingQueueName = "{$queueName}_processing";
        $this->deadLetterQueueName = "{$queueName}_dead";
    }

    public function produce($message, $priority = 1) {
        try {
            $data = [
                'id' => uniqid(),
                'message' => $message,
                'priority' => $priority,
                'attempts' => 0,
                'timestamp' => time()
            ];

            // Use sorted set for priority queue
            return $this->redis->zAdd(
                $this->queueName,
                $priority,
                json_encode($data)
            );
        } catch (Exception $e) {
            throw new Exception("Failed to produce message: " . $e->getMessage());
        }
    }

    public function consume($timeout = 0) {
        try {
            // Get highest priority message
            $messages = $this->redis->zRevRangeByScore(
                $this->queueName,
                '+inf',
                '-inf',
                ['limit' => [0, 1]]
            );

            if (empty($messages)) {
                return null;
            }

            $message = json_decode($messages[0], true);
            
            // Move to processing queue
            $this->redis->zRem($this->queueName, $messages[0]);
            $this->redis->lPush($this->processingQueueName, $messages[0]);

            return $message;
        } catch (Exception $e) {
            throw new Exception("Failed to consume message: " . $e->getMessage());
        }
    }

    public function acknowledge($messageId) {
        // Remove from processing queue
        $this->redis->lRem($this->processingQueueName, $messageId, 0);
    }

    public function retry($message, $maxAttempts = 3) {
        $message['attempts']++;
        
        if ($message['attempts'] >= $maxAttempts) {
            // Move to dead letter queue
            $this->redis->lPush(
                $this->deadLetterQueueName,
                json_encode($message)
            );
        } else {
            // Return to main queue with lower priority
            $this->produce(
                $message['message'],
                $message['priority'] - 1
            );
        }
    }

    public function processDeadLetters($callback) {
        while ($message = $this->redis->rPop($this->deadLetterQueueName)) {
            $callback(json_decode($message, true));
        }
    }
    
}