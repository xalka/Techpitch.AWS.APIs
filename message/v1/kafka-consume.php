<?php

echo "Kafka Consumer Test\n";

// Set up Kafka consumer configuration
$config = new RdKafka\Conf();
$config->set('metadata.broker.list', 'localhost:9092'); // Replace with your Kafka broker
$config->set('group.id', 0); // Consumer group ID (change as needed)
$config->set('auto.offset.reset', 'earliest'); // Start consuming from the earliest message if no offset is committed

// $conf->set('fetch.message.max.bytes', 1048576);
// $conf->set('enable.auto.commit', 'false'); // Handle commits manually for safety
// $conf->set('auto.offset.reset', 'latest'); // Or 'earliest'

$consumer = new RdKafka\KafkaConsumer($config);

// Check if the consumer is ready
if (!$consumer->getMetadata(true, null, 60e3)) {
    die("Kafka Consumer not ready!");
}

// Specify the Kafka topic to consume messages from
$topicName = "testingkafka"; // Replace with your topic name
// $topic = $consumer->newTopic($topicName);

// Subscribe to the topic
$consumer->subscribe([$topicName]);

echo "Consumer started. Listening for messages...\n";

// Consume messages
while (true) {
    $message = $consumer->consume(10000); // Consume messages from partition 0, with a 10-second timeout

    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            // No error, process the message
            echo "Consumed message: " . $message->payload . "\n";
            break;
        
        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            // End of partition reached
            echo "End of partition reached for topic: {$topicName}\n";
            break;
        
        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            // Consumer timed out
            echo "Consumer timed out while waiting for message.\n";
            break;
        
        default:
            // Handle other errors
            echo "Consumer error: " . $message->errstr() . "\n";
            break;
    }
}

// Optionally, you can use this block to cleanly close the consumer when done.
$consumer->close();

?>
