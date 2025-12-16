<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

pcntl_async_signals(true);
$keepRunning = true;

// Signal handler for graceful shutdown
pcntl_signal(SIGTERM, function () use (&$keepRunning) {
    echo "Caught SIGTERM, stopping gracefully...\n";
    $keepRunning = false;
});

$baseDir = dirname(__dir__,2);

require_once $baseDir.'/.config/.config.php'; 

$files = ['.funcs.php','.mysql.php','.mongodb.php','.procedures.php','RedisHelper.php','KafkaHelper.php','SDP.php'];

foreach ($files as $file) require_once $baseDir.'/.core/'.$file;

try {

    $kafka = new KafkaHelper(KAFKA_BROKER);

    $kafka->createConsumer("sms-worker-group", [KAFKA_SEND_BULK_TOPIC]);

    $kafka->consume(function($payload) {
        $payload = json_decode($payload,1);

        // echo "\n\nReceived & processing......\n";
        // print_r($payload); 
        // echo "\n\n";

        $headers = $payload['headers'];

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

        // 1. Get contacts from contact group if contactGroupId is set
        if(isset($payload['contactGroupId']) && $payload['contactGroupId'] > 0 ){

            $url = API_HOST."group/v1/detail";
            $loop = true;
            $start = 0;
            $limit = MESSAGE_CHUNKS;

            while($loop){
                $request = [
                    'groupId' => $payload['contactGroupId'],
                    'start' => $start,
                    'limit' => $limit
                ];
                $contacts = json_decode(callAPI('GET',$url,$headers,$request),1); 

                // 1.1 Check if its custom or bulk message
                if($payload['mode'] == 1){
                    foreach ($contacts as $contact){
                        // modify to take more custom parameter {ACCOUNT_NAME} {ACCOUNT_NUMBER} {{AMOUNT}}
                        $message['message'] = str_replace("{name}", ucfirst($contact['fname']), $message['message']);
                        $message['contacts'] = $contact['phone'];

                        $sdp = new SDP();
                        $response = json_decode($sdp->sendSMS($message),1);
                        print_r($response);

                        if( isset($response['statusCode']) && $response['statusCode'] == 'SC0000' ){
                            // save into recipient table
                            // depends on the dlr
                            // print_r($response);
                        }
                        
                    }

                // bulk
                } else {
                    // $message['contacts'] = $contacts;
                    $message['contacts'] = implode(',',array_column($contacts,'phone'));

                    $sdp = new SDP();
                    $response = json_decode($sdp->sendSMS($message),1);
                    print_r($response);

                    if( isset($response['statusCode']) && $response['statusCode'] == 'SC0000' ){
                        // save into recipient table
                        // depends on the dlr
                        // print_r($response);
                    }                    
                }

                if(count($contacts) < $limit) $loop = false;
                else $start += 1;

            }

        }

        // 2. Send from contact list

            // 2.1 Check if its custom or bulk

                // 2.1.1 Custom message

                // 2.1.2 Bulk message

                    // * Is it possible to have contact id related to message recipients
        






        




    });

} catch (Throwable $e) {
    echo "Kafka Crash: " . $e->getMessage() . PHP_EOL;
    echo "At: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo "Trace:" . PHP_EOL . $e->getTraceAsString() . PHP_EOL;

    error_log(sprintf(
        "[%s] Kafka Crash: %s in %s:%d\n%s",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ), 3, LOG_FILE_KAFKA);    
}