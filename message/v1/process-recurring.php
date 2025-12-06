<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.redis.php';

require_once __dir__.'/../../.core/Kafka/KafkaClient.php';

$kafka = new KafkaClient(KAFKA_BROKER);

$callback = function ($message) {
    echo "Received message: " . $message->payload . "\n\n";

    $payload = json_decode($message->payload,1); // print_r($payload); exit;
    
    // save into DB
    $return = SaveMessage($payload); print_r($return);
    if(isset($return['_id']) && $return['_id']>0){
        $payload['messageId'] = validInt($return['_id']);

        // TransactionId
        // 4. update mysql payment
        /*$dbdata = [
            'action' => 9,
            'paymentId' => $payload['transactionId'],
            'messageId' => $payload['messageId'],
            'groupId' => $payload['pgroupId']
        ];
        $return = PROC(Payment($dbdata)); // [0][0];        

        $response = saveMessageRecipients($payload); // print_r($response);
        if($response['status']==500){
            echo "Technical problem";
        }*/
    }

    echo "Done. \n\n";

};

// Consume messages from the topic
$groupId = "0";
$kafka->consumeMessages(KAFKA_RECURRING_SMS_TOPIC,$groupId,$callback);