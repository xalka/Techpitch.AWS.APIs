<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.redis.php'; 
// require __dir__.'/../../.core/Redis/Connection.php'; 
// require __dir__.'/../../.core/Redis/Queue.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

// Connect to Redis server
$redis = redisConn();

// Define a callback function to handle messages
$callback = function($redis, $channel, $message) {
    echo "Received message from channel $channel: $message\n";

    $message = json_decode($message,1);

    // Get the contacts from a contact groups id
    $message['contacts'] = [254715003414,254115242477];

    if( $message['method'] == 'transaction' && count($message['contacts'])==1) $message['typeId'] = 1;
    else $message['typeId'] = 2;

    $message['action'] = 1;
    $message['recepients'] = count($message['contacts']);
    $message['statusId'] = 1;
    $message['sent'] = 0;
    
    if($message['scheduled']) date('Y-m-d H:i:s',strtotime($message['scheduled']));
    else unset($message['scheduled']);
    
    $return1 = PROC(Message($message));
    if(isset($return1['created'])){
        // Chunk the array into chunks
        $chunks = array_chunk($message['contacts'],MESSAGE_CHUNKS);
        // loop recepients saving them
        foreach ($chunks as $chunk) {
            $sql = "INSERT INTO messagesRecipients(messageId,phone) VALUES";

            foreach ($chunk as $item) {
                // validate the phone numbers
                // ensure its a phone number, else skip
                $sql .= "(".$return1['messageId'].",".$item."),";
            }
            $return2 = query(rtrim($sql,','));
            print_r($return2);

            echo "\n";
            sleep(3);
        }
    }
    // print_r($return1);

    // 2. Save recepients
    $message['_id'] = (int)$return1['messageId'];
    $message['created'] = mongodate('NOW');
    // print_r($message); exit;

    // 3. send message to SDP endpoint

    // 4. update mysql of the process and unit descrease
    // 'transactionId' => $balance['transactionId']

    // 5. save into mongodb
    $return2 = mongoInsert(CMESSAGE,$message);

    if($return2){
        $response = [
            'status' => 201,
            'error' => 0
        ];
    }      

    echo "\nProcessing ended.";
};

// Subscribe to the channel
$redis->subscribe([REDIS_QUEUE], $callback);