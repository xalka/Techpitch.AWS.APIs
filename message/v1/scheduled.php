<?php

/*

Gets scheduled messages from mysql and queues them into kafka

*/

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mongodb.php';

require_once __dir__.'/../../.core/Kafka/KafkaClient.php';

// 1. Get schedule sms in mysql
$dbdata = [
    'action' => 8
];

// use mongodb instead of mysql
$filter = [
    'statusId' => 2,
    'scheduled' => [
        '$gte' => mongodate('-30 minutes'),
        '$lte' => mongodate('+1 minutes')
    ]
];
$options = [
    'projection' => [
        '_id'=>1, 'mode'=>1, 'title'=>1, 'message'=>1, 'contacts'=>1, 'alphanumericId'=>1, 'alphanumeric'=>1, 
        'transactionId'=>1, 'typeId'=>1, 'type'=>1,
        'customerId'=>1, 'pgroupId'=>1,
        'units'=>1, 'recipients'=>1, 'status'=>1, 'statusId'=>1, 'sent'=>1, 'created'=>1, 'scheduled'=>1, 'contactGroupId'=>1
    ],	
    'sort' => ['scheduled' => 1], // ORDER BY scheduled ASC
    'limit' => 100
];

try {
    // $messages = PROC(Message($dbdata)); // [0];
    // echo "\nMysql\n";
    // print_r($messages);
    // echo "\n\nMongo\n";
    $messages = mongoDateTime(mongoSelect(CMESSAGE,$filter,$options));

    if(empty($messages)){
        print_j(['status' => 200,'message' => 'No scheduled messages']);
        exit;
    }
    
    $kafkaClient = new KafkaClient(KAFKA_BROKER);

    // 2. Queue into kafka for processing
    foreach ($messages as $message){
        $request = [
            '_id' => $message->_id,
            'messageId' => $message->_id,
            'title' => $message->title,
            'message' => $message->message,
            'contacts' => $message->contacts,
            'recipients' => $message->recipients, // Fixed typo from 'recepients' to 'recipients'
            'alphanumeric' => $message->alphanumeric, 
            'alphanumericId' => $message->alphanumericId,
            'scheduled' => $message->scheduled,
            'transactionId' => $message->transactionId,
            'customerId' => $message->customerId,
            'pgroupId' => $message->pgroupId,
            'units' => $message->units,
            'statusId' => $message->statusId,
            'typeId' => $message->typeId,
            'typeId' => $message->type == 'transaction' ? 1 : 2,
            'mode' => $message->mode == 'custom' ? 1 : 0
        ];

        $kafkaClient->produceMessage(KAFKA_SEND_BULK_TOPIC, json_encode($request),$partition=RD_KAFKA_PARTITION_UA);
        
        // update mysql
        $dbdata = [
            'action' => 4,
            'messageId' => $request['messageId'],
            'pgroupId' => $request['pgroupId'],
            'statusId' => 4
        ];
        $return1 = PROC(Message($dbdata))[0][0];
        
        if($return1['updated']==1){
            $filter = [
                '_id' => validInt($dbdata['messageId']),
                'pgroupId' => validInt($dbdata['pgroupId'])
            ];
            $update = [
                'statusId' => 4,
                'status' => 'queued',
                'updated' => mongodate('NOW')
            ];
            $return2 = mongoUpdate(CMESSAGE,$filter,$update);  
            // print_r($return2);

            // $response = [
            //     'status' => 200
            // ]; 

        } else {
            $response = [
                'status' => 400,
                'message' => "Unable to update mysql"
            ];             
        }
    }
    
    // Flush pending messages
    $kafkaClient->flush();
    

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";     
};