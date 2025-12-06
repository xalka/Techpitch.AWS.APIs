<?php
require 'vendor/autoload.php';

use RdKafka\Producer;

function setupKafkaProducer($brokerList)
{
    $producer = new Producer();
    $producer->setLogLevel(LOG_DEBUG);
    $producer->addBrokers($brokerList);
    return $producer;
}

function pushToKafkaQueue($producer, $topicName, $data)
{
    $topic = $producer->newTopic($topicName);
    
    $jsonData = json_encode($data);

    $result = $topic->produce(RD_KAFKA_PARTITION_UA, 0, $jsonData);

    $producer->poll(0);

    // Ensure all messages are delivered
    for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
        $result = $producer->flush(1000);
        if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
            return "Message successfully produced to topic: $topicName";
        }
    }

    throw new Exception("Failed to flush messages to Kafka after retries.");
}

// Configuration
$brokerList = "localhost:9092"; // Change as per your Kafka configuration

$bulkTopic = "bulk_queue";
$transactionalTopic = "transactional_queue";

// Sample Data
$bulkData = [
    'id' => 1,
    'type' => 'bulk',
    'payload' => 'This is bulk data'
];

$transactionalData = [
    'id' => 2,
    'type' => 'transactional',
    'payload' => 'This is transactional data'
];

try {
    // Setup Kafka Producer
    $producer = setupKafkaProducer($brokerList);

    // Push to bulk queue
    echo pushToKafkaQueue($producer, $bulkTopic, $bulkData) . PHP_EOL;

    // Push to transactional queue
    echo pushToKafkaQueue($producer, $transactionalTopic, $transactionalData) . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
?>
