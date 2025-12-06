<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
// require __dir__.'/../../.core/.mysql.php'; 
// require __dir__.'/../../.core/.procedures.php';

require_once __dir__.'/../../.core/Kafka/KafkaClient.php';

$groupId = "0";

$kafkaClient = new KafkaClient(KAFKA_BROKER);

/// Define a callback to handle incoming messages
$callback = function (RdKafka\Message $message) {
    echo "Received message: " . $message->payload . "\n";
    writeToFile(LOG_FILE, $message);

    $payload = json_decode($message->payload, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Invalid JSON payload: " . $message->payload . "\n";
        return;
    }
    print_r($payload);
};

$kafkaClient->consumeMessages(KAFKA_SEND_BULK_TOPIC, $groupId, $callback);