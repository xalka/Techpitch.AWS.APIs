<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

$baseDir = dirname(__dir__,2);

require_once $baseDir.'/.config/.config.php'; 
$files = ['.funcs.php','.mysql.php','.mongodb.php','.procedures.php','KafkaHelper.php'];
foreach ($files as $file) require_once $baseDir.'/.core/'.$file;

try {
    $kafka = new KafkaHelper(KAFKA_BROKER);
    $kafka->createConsumer("contact-worker-group", [KAFKA_GROUP_CONTACTS_CREATE]);
    
    $kafka->consume(function($payload) {
        // print_r($payload);
        echo "\nReceived & processing......\n";
        $payload = json_decode($payload,1);

        // print_r($payload); echo "\n\n";

        foreach ($payload['contacts'] as $contact) {
            // save into mysql
            $dbData = [
                'action' => 4,
                'groupId' => (int)$payload['groupId'],
                'pgroupId' => (int)$payload['pgroupId'],
                'phone' => validPhone($contact['phone']),
                'fname' => $contact['fname'],
                'lname' => $contact['lname']
            ];

            // print_r($dbData); echo "\n\n";
            
            $return1 = PROC(ContactGroup($dbData));

            // print_r($return1); echo "\n\n";

            // save into mongoDb list of contacts
            if(isset($return1[0][0]['created']) && $return1[0][0]['created'] > 0){
                $dbData['_id'] = (int)$return1[0][0]['id'];
                unset($dbData['action']);
                $return2 = mongoInsert(GCONTACT,$dbData);

                // print_r($return2); echo "\n\n";

                // print_r($return2);
                // check if mongoDb fails
                echo "\n\nDone.\n";
            }
        }

    });


} catch (\Throwable $th) {
    echo "Kafka Crash: " . $th->getMessage() . PHP_EOL;
    error_log("Error: " . $th->getMessage() . " in " . $th->getFile() . ":" . $th->getLine());
}