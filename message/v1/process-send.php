<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

$baseDir = dirname(__dir__,2);

require_once $baseDir.'/.config/.config.php'; 

$files = ['.funcs.php','.mysql.php','.mongodb.php','.procedures.php','RedisHelper.php','KafkaHelper.php','SDP.php'];

foreach ($files as $file) require_once $baseDir.'/.core/'.$file;

try {

    echo "Connecting to Kafka...\n";
    $kafka = new KafkaHelper(KAFKA_BROKER);

    echo "Subscribing to topic sendbulksms...\n";
    $kafka->createConsumer("sms-worker-group", [KAFKA_SEND_BULK_TOPIC]);

    echo "Listening...\n";

    $kafka->consume(function($payload) {
        $payload = json_decode($payload,1);

        $redis = new RedisHelper();
        $token = $redis->get(SDP_TOKEN);

        $message = [
            'token' => $token,
            'username' => SDP_USERNAME2,
            "password" => SDP_PASSWORD2,
            'shortcode' => $payload['alphanumeric'],
            'timestamp' => SDP_TIMESTAMP,
            'messageId' => $payload['messageId'],
            'message' => $payload['message'],
        ];
        
        // 1. Send from contact group
        if(isset($payload['contactGroupId']) && $payload['contactGroupId'] > 0 ){

            $url = API_HOST."group/v1/detail";
            $loop = true;
            $start = 0;
            $limit = MESSAGE_CHUNKS;

            while($loop){
                $request = [
                    'id' => $payload['contactGroupId'],
                    'start' => $start,
                    'limit' => $limit
                ];
                $contacts = json_decode(callAPI('GET',$url,$headers,$request),1); 

                // $payload['contacts'] = $contacts[0]['contacts'];

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
                            // $message['contacts'] = [$contact];
                            $message['message'] = str_replace("{name}", ucfirst($contact['fname']), $message['message']);
                            
                            // $return = json_decode(bulkSDP($message),1);


                            $payload = [
                                'token' => $token,
                                'username' => SDP_USERNAME2,
                                "password" => SDP_PASSWORD2,
                                'shortcode' => SDP_ALPHANUMERIC,
                                'timestamp' => SDP_TIMESTAMP,
                                'contacts' => $contact['contacts'],
                                'message' => str_replace("{name}", ucfirst($contact['fname']), $message['message']),
                                'messageId' => 'Tp'.(String)time(),
                            ];

                            $sdp = new SDP();
                            // $response = json_decode($sdp->sendSMS($payload),1);
                            $response = $sdp->sendSMS($payload);
                            print_r($response);                            
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
        }

        // 2. send from contact list
        elseif(isset($payload['contacts']) && !empty($payload['contacts']) && count($payload['contacts']) > 0 ){

            if($payload['mode'] == 1){ 
                foreach ($payload['contacts'] as $contact){ 
                    $message['contacts'] = $contact['phone'];
                    $message['message'] = str_replace("{name}", ucfirst($contact['fname']), $message['message']);
                    
                    $sdp = new SDP();
                    $response = json_decode($sdp->sendSMS($message),1);
                    print_r($response); 
                    exit;
                }
            
            } else {

                $chunks = array_chunk($payload['contacts'],MESSAGE_CHUNKS);
                
                foreach ($chunks as $chunk){ 
                    $message['contacts'] = implode(',',array_column($chunk,'phone'));
                    $sdp = new SDP();
                    $response = json_decode($sdp->sendSMS($message),1);
                    print_r($response);
                }
                
            }

        }



    });

} catch (Throwable $e) {
    echo "Kafka Crash: " . $e->getMessage() . PHP_EOL;
}