<?php

use RdKafka\Producer;
use RdKafka\Consumer;
use RdKafka\ConsumerTopic;
use RdKafka\Message;

class KafkaClient
{
    private $brokers;

    public function __construct(string $brokers)
    {
        $this->brokers = $brokers;
    }

    /**
     * Produce a message to a Kafka topic with priority.
     * Higher priority messages are sent to the first partition.
     *
     * @param string $topicName
     * @param string $message
     * @param int $priority (1 = high priority, 2 = normal, 3 = low)
     */
    public function produceMessage(string $topicName, string $message, int $priority = 2): void
    {
        $producer = new Producer();
        $producer->addBrokers($this->brokers);

        $topic = $producer->newTopic($topicName);

        // Determine partition based on priority
        $partition = match ($priority) {
            1 => 0, // High priority
            2 => 1, // Normal priority
            3 => 2, // Low priority
            default => RD_KAFKA_PARTITION_UA, // Auto assign if invalid
        };

        $topic->produce($partition, 0, $message);
        $producer->flush(10000);
    }

    /**
     * Consume messages from a Kafka topic.
     *
     * @param string $topicName
     * @param string $groupId
     * @param callable $callback Function to handle each message
     */
    public function consumeMessages(string $topicName, string $groupId, callable $callback)
    {
        // $conf = new \RdKafka\Conf();
        $conf = new RdKafka\Conf();
        $conf->set('group.id', $groupId);
        $conf->set('metadata.broker.list', $this->brokers);

        // Set offset reset policy
        $conf->set('auto.offset.reset', 'earliest');

        $consumer = new Consumer($conf);
        $consumerTopic = $consumer->newTopic($topicName);

        // Start consuming from all partitions
        $consumerTopic->consumeStart(0, RD_KAFKA_OFFSET_END); // If you don’t need to consume from a specific partition, do not set the partition manually:
        // $consumerTopic->consumeStart(RD_KAFKA_PARTITION_UA, RD_KAFKA_OFFSET_END); # to consume automatically from available partitions:

        echo "Consumer started. Listening to topic '{$topicName}'...\n";

        while (true) {
            $message = $consumerTopic->consume(RD_KAFKA_PARTITION_UA, 120 * 1000); // 120 seconds timeout
            if ($message === null) {
                continue;
            }

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    // Process the message
                    // $callback($message);
                    echo "Processing the message";
                    return $message->payload;
                    break;

                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "End of partition reached.\n";
                    break;

                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "Consumer timed out.\n";
                    break;

                default:
                    echo "Consumer error: " . $message->errstr() . "\n";
                    break;
            }
        }
    }
}

