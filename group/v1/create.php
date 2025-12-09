<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 
require_once __dir__.'/../../.core/Kafka/KafkaClient.php';

if(!ReqPost()) ReqBad();

$headers = getallheaders();

$req = json_decode(file_get_contents('php://input'),1);

$dbdata = [
    'action' => 1,
    'customerId' => $headers['Customerid'],
    'pgroupId' => $headers['Groupid'],
    'title' => validString($req['title']),
    // 'message' => $req['message']
];

try {
    // 4. Save into mysql
    $return = PROC(ContactGroup($dbdata))[0][0]; // Done

    if(isset($return['created']) && $return['created'] > 0){
        $pgroupId = $headers['Groupid'];
        $groupId = $return['groupId'];

        $group = [
            '_id' => (int)$groupId,
            'pgroupId' => (int)$pgroupId,
            'title' => $req['title'],
            'contacts' => count($req['contacts']),
            'created' => mongodate('NOW')
        ];

        $return2 = mongoInsert(CGROUP,$group);

        // foreach ($req['contacts'] as $key => $contact) {
        //     $req['contacts'][$key]['phone'] = validPhone($contact['phone']);
        // }        

        // Queue the contacts
        foreach ($req['contacts'] as $key => $value) {
            $value['pgroupId'] = $pgroupId;
            $value['groupId'] = $groupId;

            $kafkaClient = new KafkaClient(KAFKA_BROKER);
            $kafkaClient->produceMessage(KAFKA_GROUP_CONTACTS_CREATE, json_encode($value),$partition=RD_KAFKA_PARTITION_UA);

        }            
        // Flush pending messages
        $kafkaClient->flush();

        $response = [
            'status' => 200,
            'message' => "Processing"
        ];

    } else {
        $response = [
            'status' => 401,
            'error' => "Failed to save the contacts"
        ];         
    }

} catch (Exception $e) {
    $response = [
        'status' => 401,
        'error' => $e->getMessage()
    ];    
}

// 7. Respond with a json
print_j($response);