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
    echo "Received message: " . $message->payload . "\n";
    
    $payload = json_decode($message->payload,1);

    $headers = $payload['headers'];

    /*if($payload['statusId'] == 4){
        // save into DB
        $return = SaveMessage($payload);
        if(isset($return['_id']) && $return['_id']>0){
            $payload['messageId'] = validInt($return['_id']);
        } else {
            // Exit
            echo "Error: $return[0]\n\n";
            exit;
        }
    }*/

    // Contacts from GroupId 
    if(!isset($payload['contacts']) || empty($payload['contacts']) || count($payload['contacts'])==0){
        
        $url = API_HOST."group/v1/detail";
        $loop = true;
        $start = 0;
        $limit = 10;

        while($loop){
            $request = [
                'id' => $payload['contactGroupId'],
                'start' => $start,
                'limit' => $limit
            ];
            $contacts = json_decode(callAPI('GET',$url,$headers,$request),1); // print_r($contacts); exit;

            $payload['contacts'] = $contacts[0]['contacts'];

            // implode(',',array_column($payload['contacts'],'phone'))

            $response = saveMessageRecipients($payload);
            if($response['status']==500){
                echo "Technical problem";
            }          

            // send to sdp
            // check if its normal or custom
            if($payload['mode']==1){
                $chunks = array_chunk($payload['contacts'],MESSAGE_CHUNKS);
                
                foreach ($chunks as $chunk){
                    foreach ($chunk as $contact){
                        // advance: {variables} to be parameter or read from excel column
                        $message = $payload;
                        $message['contacts'] = [$contact];
                        $message['message'] = str_replace("{name}", ucfirst($contact['fname']), $message['message']);
                        
                        $return = json_decode(bulkSDP($message),1);
                    }
                    // sleep(1);
                }

            } else {
                $return = json_decode(bulkSDP($payload),1);
            }
            
            if(count(array_column($contacts[0]['contacts'],'phone'))==$limit) $start += 1;
            else $loop = false;
            
            // $payload['contacts'] = array_merge($payload['contacts'],$contacts[0]['contacts']);
        }
    
    
    // Contact list
    } else {
        $chunks = array_chunk($payload['contacts'],MESSAGE_CHUNKS);
        
        foreach ($chunks as $chunk){ 
            if($payload['mode']==1){ 
                foreach ($chunk as $contact) { 
                    // advance: {variables} to be parameter or read from excel column
                    $message = $payload;
                    $message['contacts'] = [$contact];
                    $message['message'] = str_replace("{name}", $contact['fname'], $message['message']);
                    
                    $return = json_decode(bulkSDP($message),1);
                }    
                sleep(1);
            } else {
                $message = $payload;
                $message['contacts'] = $chunk;
                // print_r($message); echo "\n\n\n";
                $return = json_decode(bulkSDP($message),1);
            }
        }
    }
    
    if(isset($return['status']) && $return['status'] == 'SUCCESS'){
        $messageId = validInt($payload['messageId']);

        // 4. update mysql message to processing status
        $dbdata = [
            'action' => 4,
            'messageId' => $messageId,
            'pgroupId' => $payload['pgroupId'],
            'statusId' => 5
        ];
        $return = PROC(Message($dbdata));

        // 4. update mysql payment
        $dbdata = [
            'action' => 9,
            'paymentId' => $payload['transactionId'],
            'messageId' => $messageId,
            'groupId' => $payload['pgroupId']
        ];
        $return = PROC(Payment($dbdata)); // [0][0];

        // update mongo
        $filter = [ '_id' => $messageId ];
        $update = [ 
            'status' => 'processing',
            'statusId' => 5
        ];
        $return2 = mongoUpdate(CMESSAGE,$filter,$update); // print_r($return2); exit;

        echo "Done: $messageId \n\n";
    }
    
};

// Consume messages from the topic
$groupId = "0";
$kafka->consumeMessages(KAFKA_SEND_BULK_TOPIC,$groupId,$callback);