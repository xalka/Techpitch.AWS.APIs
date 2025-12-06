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

function SaveMessage($payload){
    // 1. save into database
    $dbdata = [
        'action' => 1,
        'recepients' => count($payload['contacts']),
        'title' => $payload['title'],
        'message' => $payload['message'],
        'alphanumeric' => $payload['alphanumeric'],
        'alphanumericId' => $payload['alphanumericId'],
        'transactionId' => $payload['transactionId'],
        'customerId' => $payload['customerId'],
        'pgroupId' => $payload['pgroupId'],
        'units' => $payload['units'],
        'statusId' => 2,
        'sent' => 0
    ];
    
    if( $payload['method'] == 'transaction' && count($payload['contacts'])==1) $dbdata['typeId'] = 1;
    else $dbdata['typeId'] = 2;

    if($payload['scheduled']) $dbdata['scheduled'] = date('Y-m-d H:i:s',strtotime($payload['scheduled']));
    else unset($payload['scheduled']);  
    
    $return = PROC(Message($dbdata))[0][0];

    // 2. save into mongodb
    if(isset($return['created']) && $return['created']>0){
        $dbdata['_id'] = $return['messageId'];
        $dbdata['method'] = $payload['method'];
        $dbdata['contacts'] = $payload['contacts'];
        $dbdata['status'] = 'queued';
        $dbdata['sent'] = 0;
        $dbdata['created'] = mongodate('NOW');
        unset($dbdata['action']);
    
        foreach ($dbdata as $key => $value) {
            if(is_numeric($value)) $dbdata[$key] = validInt($value);
        }        
        mongoInsert(CMESSAGE,$dbdata);
    }
    
    return $return;
}

function bulkSDP($payload){
    $token = json_decode(redisGetValue(SDP_TOKEN),1);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest',
        'X-Authorization: Bearer '.$token
    ];
    $request = [
        "timeStamp" => SDP_TIMESTAMP,
        "dataSet" => [
            [
                "userName" => SDP_USERNAME2,
                "channel" => "SMS",
                "packageId" => SDP_PACKAGE_ID, // in case you have multiple package id in your account, don’t specify the package id, remove it completely
                "oa" => $payload['alphanumeric'],
                "cpPassword" => md5(SDP_ID.SDP_PASSWORD2.SDP_TIMESTAMP),
                "msisdn" => implode(',',$payload['contacts']),
                "message" => $payload['message'],
                "uniqueId" => $payload['messageId'],
                "actionResponseURL" => SDP_CALLBACK."sdp/v1/dlrbulk"
            ]
        ]
    ];
    writeToFile(__dir__.'/logs/'.date('Y-m-d').'_sdp-bulk.log', json_encode($request,JSON_PRETTY_PRINT));
    return json_encode([
        "keyword" => "Bulk",
        "status" => "SUCCESS",
        "statusCode" => "SC0000"
    ]);
    return callAPI("POST", SDP1.'api/public/CMS/bulksms', $headers, $request);    
}

$callback = function ($message) {
    echo "Received message: " . $message->payload . "\n";
    // Example: Process the message, send it to an endpoint, or insert into a database
    // sendToEndpoint($message->payload);
    // insertIntoDatabase($message->payload);

    // get contacts from groupId
    // $contacts = "254715003414,254115242477,254728642504,254722636396,254710543307";

    $payload = json_decode($message->payload,1);  

    // scheduled message
    

    $return1 = SaveMessage($payload);

    if(isset($return1['created']) && $return1['created']>0){
        // Chunk the array into chunks
        $chunks = array_chunk($payload['contacts'],MESSAGE_CHUNKS);
        $messageId = validInt($return1['messageId']);

        // loop recepients saving them
        foreach ($chunks as $chunk) {
            $values = [];
            foreach ($chunk as $contact) {
                $values[] = "($messageId, '$contact')";
            }
            $sql = "INSERT INTO messagesRecipients(messageId,phone) VALUES ".implode(',', $values);
            $return2 = query($sql);     
            print_r($return2);
        }

        // preferable to push to SDP at this point

        // 3. send to sdp
        $payload['messageId'] = $messageId;
        $return4 = json_decode(bulkSDP($payload),1); // bulkSDP($payload);

        if(isset($return4['status']) && $return4['status'] == 'SUCCESS'){

            // 4. update mysql message  
            $dbdata = [
                'action' => 4,
                'statusId' => 3
            ];
            $return = PROC(Message($dbdata)); // [0][0];
            // print_r($return);

            // 4. update mysql payment
            $dbdata = [
                'action' => 9,
                'paymentId' => $payload['transactionId'],
                'customerId' => $messageId
            ];
            $return = PROC(Payment($dbdata)); // [0][0];
            // print_r($return);
            
            // update mongo
            // mongoUpdate(CMESSAGE,['_id'=>$messageId],['status'=>'processing','statusId'=>3]);

            // update mongo
            $filter = [ '_id' => $messageId ];
            $update = [ 
                'status' => 'processing',
                'statusId' => 3
            ];
            $return2 = mongoUpdate(CMESSAGE,$filter,$update);        
        }

    } else {
        // log failed to save
        $response = [
            'status' => 403,
            'message' => "Failed to save into mysql"
        ];           
    }
    
};

// Consume messages from the topic
$groupId = "0";
$kafka->consumeMessages(KAFKA_SEND_BULK_TOPIC,$groupId,$callback);