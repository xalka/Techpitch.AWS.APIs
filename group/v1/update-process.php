<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

$baseDir = dirname(__dir__,2);

require_once $baseDir.'/.config/.config.php'; 
$files = ['.funcs.php','.mysql.php','.mongodb.php','.procedures.php','KafkaHelper.php'];
foreach ($files as $file) require_once $baseDir.'/.core/'.$file;

try {
    $kafka = new KafkaHelper(KAFKA_BROKER);
    $kafka->createConsumer("contact-worker-group", [KAFKA_GROUP_CONTACTS_UPDATE]);
    
    $kafka->consume(function($payload) {
        // print_r($payload);
        echo "\nReceived & processing......\n";
        $payload = json_decode($payload,1);

        $groupId = (int)$payload['groupId'];
        $pgroupId = (int)$payload['pgroupId'];

        foreach ($payload['contacts'] as $contact) {
            // update mysql
            $dbData = [
                'action' => 5,
                'id' => (int)$contact['id'],
                'groupId' => $groupId,
                'pgroupId' => $pgroupId,
                'phone' => validPhone($contact['phone']),
                'fname' => validString($contact['fname']),
                'lname' => validString($contact['lname'])
            ];

            echo "\n\nDb :\n";
            print_r($dbData);  echo "\n\n";
            
            $return1 = PROC(ContactGroup($dbData));

            echo "\n\nMysql\n";
            print_r($return1); echo "\n\n";

            // save into mongoDb list of contacts
            if(isset($return1[0][0]['id']) && $return1[0][0]['id'] > 0){

                $filter = [
                    '_id' => (int)$return1[0][0]['id'],
                    'groupId' => $groupId,
                    'pgroupId' => $pgroupId
                ];
                unset($dbData['id']);
                unset($dbData['action']);
                unset($dbData['groupId']);
                unset($dbData['pgroupId']);

                $dbData['updatedAt'] = mongodate('NOW');

                echo "\n\nDb into mongo :\n";
                print_r($dbData);  echo "\n\n";

                $return2 = mongoInsertOrUpdate(GCONTACT,$filter,$dbData);

                echo "\n\nMongo\n";
                print_r($return2); echo "\n\n";

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