<?php

class KafkaHelper {

    private string $brokers;
    private ?RdKafka\Producer $producer = null;
    private ?RdKafka\KafkaConsumer $consumer = null;

    public function __construct(string $brokers) {
        $this->brokers = $brokers;
    }

    /* ============================================================
     *  PRODUCER SECTION
     * ============================================================
     */

    private function getProducer(): RdKafka\Producer {
        if ($this->producer === null) {
            $conf = new RdKafka\Conf();
            $conf->set('metadata.broker.list', $this->brokers);

            // Ensures batch performance
            $conf->set('queue.buffering.max.messages', "1000000"); // handle large bursts
            $conf->set('queue.buffering.max.kbytes', "2097152"); // 2GB if possible
            $conf->set('queue.buffering.max.ms', "5"); 

            $conf->set('batch.num.messages', "50000"); // large adaptive batch
            $conf->set('compression.type', "lz4");
            $conf->set('linger.ms', 5);

            $conf->set('enable.auto.commit', 'false'); // Best practice: manual commit
            $conf->set('auto.offset.reset', 'earliest');

            $this->producer = new RdKafka\Producer($conf);
        }

        // Verify if the producer is ready
        if (!$this->producer->getMetadata(true, null, 60e3)) {
            throw new Exception("Kafka Producer not ready!");
        }         

        return $this->producer;
    }  

    public function produceMessage(string $topic, string $message, int $partition = RD_KAFKA_PARTITION_UA): void
    {
        $topicObj = $this->getProducer()->newTopic($topic);
        $topicObj->produce($partition, 0, $message);
    }

    public function produceBatch(string $topic, array $messages, int $partition = RD_KAFKA_PARTITION_UA): void
    {
        $topicObj = $this->getProducer()->newTopic($topic);

        foreach ($messages as $msg) {
            $topicObj->produce($partition, 0, $msg);
        }
    }

    /**
     * Flush producer queue
     * @param int $timeoutMs
     * @return bool
     */
    public function flush(int $timeoutMs = 10000): bool
    {
        $producer = $this->getProducer();

        $result = RD_KAFKA_RESP_ERR_NO_ERROR;
        for ($i = 0; $i < 10; $i++) { // retry flush 10 times
            $result = $producer->flush($timeoutMs);
            if ($result === RD_KAFKA_RESP_ERR_NO_ERROR) {
                return true;
            }
        }

        error_log("Kafka Flush Failed: " . $result);
        return false;
    }


    /* ============================================================
     *  CONSUMER SECTION
     * ============================================================
     */

    public function createConsumer(string $groupId, array $topics): void
    {
        $conf = new RdKafka\Conf();
        $conf->set('group.id', $groupId);
        $conf->set('metadata.broker.list', $this->brokers);

        // Auto commit offsets
        $conf->set('enable.auto.commit', 'true'); // 'false' => 'This avoids losing any SMS in case of server crash.'

        // Ensures partitions are balanced across consumers
        $conf->set('partition.assignment.strategy', 'roundrobin');

        // Emit events as messages for logs/debug
        $conf->setErrorCb(function ($kafka, $err, $reason) {
            error_log("Kafka Error: " . rd_kafka_err2str($err) . " Reason: " . $reason);
        });


        $this->consumer = new RdKafka\KafkaConsumer($conf);
        $this->consumer->subscribe($topics);
    }

    /**
     * Run consumer loop
     * @param callable $callback function($payload) {}
     */
    public function consume(callable $callback): void {
        if ($this->consumer === null) {
            throw new \Exception("Consumer not created. Call createConsumer() first.");
        }

        while (true) {
            $msg = $this->consumer->consume(3000);

            switch ($msg->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $callback($msg->payload);
                    break;

                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    // End of partition, just continue
                    break;

                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // Timeout waiting for message
                    break;

                default:
                    error_log("Kafka consumer error: " . $msg->errstr());
                    break;
            }
        }
    }
}
