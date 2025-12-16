<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mongodb.php';
// require __dir__.'/../../.core/.redis.php';

require_once __dir__.'/../../.core/Kafka/KafkaClient.php';

$kafka = new KafkaClient(KAFKA_BROKER);

$callback = function ($message) {

    echo "Received message: " . $message->payload . "\n";
    
    $payload = json_decode($message->payload,1);

    $payload['action'] = 4;
    $payload['pgroupId'] = (int)$payload['pgroupId'];
    $payload['groupId'] = (int)$payload['groupId'];
    $payload['phone'] = validPhone($payload['phone']);

    $return = PROC(ContactGroup($payload))[0][0]; // Done

    // $sql = "INSERT INTO groupContacts(groupId,phone,fname,lname) VALUES ".implode(',', $values);
        
    // 2. Validate

    // $contacts = [
    //     '_id' => validInt($return['groupId']),
    //     'groupId' => validInt($return['groupId']),
    //     'pgroupId' => validInt($headers['Groupid']),
    //     'title' => $dbdata['title'],
    //     'contacts' => $req['contacts'],
    //     'active' => 1,
    //     'created' => mongodate('NOW')
    // ];

    unset($payload['action']);
    $payload['_id'] = (int)$return['id'];
    $payload['active'] = 1;
    $payload['created'] = mongodate('NOW');

    $return2 = mongoInsert(GCONTACT,$payload);

    if($return2){
        $response = [
            'status' => 201
        ];
    }
    
};

// Consume messages from the topic
$groupId = "0";
$kafka->consumeMessages(KAFKA_GROUP_CONTACTS_CREATE,$groupId,$callback);