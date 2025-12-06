<?php

// worker.php

require_once 'RedisConnection.php';
require_once 'AdvancedQueue.php';

$queue = new AdvancedQueue('my_queue');

while (true) {
    try {
        $message = $queue->consume();
        
        if ($message) {
            // Process message
            processMessage($message);
            
            // Acknowledge successful processing
            $queue->acknowledge($message['id']);
        } else {
            // No messages, sleep for a bit
            sleep(1);
        }
    } catch (Exception $e) {
        // Handle error and retry
        $queue->retry($message);
        echo "Error processing message: " . $e->getMessage() . "\n";
    }
}

function processMessage($message) {
    // Implement your message processing logic here
    echo "Processing message: " . json_encode($message) . "\n";
    // Simulate processing time
    sleep(1);
}