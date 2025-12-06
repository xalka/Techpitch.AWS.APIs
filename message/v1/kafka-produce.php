<?php

echo "Kafka producer test\n";

// Set up the Kafka producer configuration
$config = new RdKafka\Conf();
$config->set('metadata.broker.list', 'localhost:9092'); // Replace with your Kafka broker

// $conf->set('queue.buffering.max.messages', 100000);
// $conf->set('batch.num.messages', 1000);
// $conf->set('linger.ms', 5);  // Delay batching to gather more messages
// $conf->set('compression.codec', 'snappy'); // gzip, lz4, zstd, etc.

// Create a producer instance
$producer = new RdKafka\Producer($config);

// Check if the producer is ready
if (!$producer->getMetadata(true, null, 60e3)) {
    die("Kafka Producer not ready!");
}

// Specify the Kafka topic to produce messages to
$topicName = "testingkafka"; // Replace with your topic name
$topic = $producer->newTopic($topicName);

// Produce messages to the Kafka topic
for ($i = 0; $i < 10; $i++) {
    $message = "Message " . $i .' '.time();
    $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
    echo "Produced: $message\n";
    sleep(1);
}

// Wait for all messages to be sent
$producer->flush(10000); // Wait for up to 10 seconds

echo "All messages sent!\n";
