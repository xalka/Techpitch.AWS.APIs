<?php

class KafkaClient
{
    private $producer;
    private $brokers;

    /**
     * KafkaClient constructor.
     * @param string $brokers
     */
    public function __construct(string $brokers){
        $this->brokers = $brokers;

        // Set up Kafka producer configuration
        $config = new RdKafka\Conf();
        $config->set('metadata.broker.list', $this->brokers);

        $config->set('queue.buffering.max.messages', 100000);
        $config->set('batch.num.messages', 1000);
        $config->set('linger.ms', 5);  // Delay batching to gather more messages
        $config->set('compression.codec', 'lz4'); // gzip, snappy, lz4, zstd, etc.

        // Create a producer instance
        $this->producer = new RdKafka\Producer($config);

        // Verify if the producer is ready
        if (!$this->producer->getMetadata(true, null, 60e3)) {
            throw new Exception("Kafka Producer not ready!");
        }
    }

    /**
     * Produce a single message to a Kafka topic.
     * 
     * @param string $topicName
     * @param string $message
     * @param int $partition
     */
    public function produceMessage(string $topicName, string $message, int $partition = RD_KAFKA_PARTITION_UA): void {
        $topic = $this->producer->newTopic($topicName);       

        // Send the message to the topic
        $topic->produce($partition, 0, $message);
        $this->producer->poll(0);
        // echo "Produced: $message\n";
    }

    /**
     * Produce multiple messages to a Kafka topic.
     * 
     * @param string $topicName
     * @param array $messages
     * @param int $partition
     */
    public function produceMessages(string $topicName, array $messages, int $partition = RD_KAFKA_PARTITION_UA): void {
        $topic = $this->producer->newTopic($topicName);

        foreach ($messages as $message) {
            $topic->produce($partition, 0, $message);
            // echo "Produced: $message\n";
        }
        $this->producer->poll(0);
    }

    /**
     * Flush all pending messages.
     * 
     * @param int $timeout
     */
    public function flush(int $timeout = 10000): void {
        $this->producer->flush($timeout);
    }

/**
     * Consume messages from a Kafka topic and process them using a callback function.
     * 
     * @param string $topicName
     * @param string $groupId
     * @param callable $callback
     * @param int $timeout
     */
    public function consumeMessages(string $topicName, string $groupId, callable $callback, int $timeout = 100): void {
        $config = new RdKafka\Conf();
        $config->set('metadata.broker.list', $this->brokers);
        $config->set('group.id', $groupId);
        $config->set('auto.offset.reset', 'earliest'); // earliest or latest
        $config->set('fetch.min.bytes', '1');

        $config->set('fetch.message.max.bytes', 1048576);
        $config->set('fetch.wait.max.ms', '10');
        $config->set('enable.auto.commit', 'true'); // Automatically commit offsets
        $config->set('auto.commit.interval.ms', '5000'); // Commit offsets every 5 seconds

        $consumer = new RdKafka\KafkaConsumer($config);
        $consumer->subscribe([$topicName]);

        echo "Consumer started. Listening to topic '{$topicName}'...\n";

        while (true) {
            $message = $consumer->consume($timeout);

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    // Process the message
                    $callback($message);
                    $consumer->commit($message);
                    break;

                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "End of partition reached.\n";
                    break;

                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // echo "Consumer timed out.\n";
                    break;

                default:
                    echo "Consumer error: " . $message->errstr() . "\n";
                    break;
            }
        }
    }    

}

