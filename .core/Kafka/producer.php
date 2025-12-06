<?php

function produceMessage(string $broker, string $topic, string $message, int $partition = RD_KAFKA_PARTITION_UA): void {
    $conf = new RdKafka\Conf();
    $conf->set('metadata.broker.list', $broker);

    $producer = new RdKafka\Producer($conf);
    if (!$producer) {
        throw new Exception("Failed to create producer");
    }

    $kafkaTopic = $producer->newTopic($topic);
    $kafkaTopic->produce($partition, 0, $message);
    $producer->poll(0);

    // Ensure all messages are sent before exiting
    $producer->flush(10000);
}
