<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
// require __dir__.'/../../.core/.funcs.php'; 
// require __dir__.'/../../.core/.mysql.php'; 
// require __dir__.'/../../.core/.procedures.php';

require_once __dir__.'/../../.core/Kafka/KafkaClient.php';

// $brokers = 'localhost:9092'; // Replace with your Kafka broker address
// $topic = 'test-topic';

$groupId = 1;

$kafkaClient = new KafkaClient(KAFKA_BROKER);

// Define a callback to handle incoming messages
$callback = function (Message $message) {
    echo "Received message: " . $message->payload . "\n";
};

$kafkaClient->consumeMessages(KAFKA_BULK_TOPIC, $groupId, $callback);

print_r($kafkaClient);