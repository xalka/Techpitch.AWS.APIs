<?php

$baseDir = dirname(__dir__,2);

require_once $baseDir.'/.config/.config.php'; 
$files = ['.funcs.php','.mysql.php','.mongodb.php','.procedures.php','KafkaHelper.php'];
foreach ($files as $file) require_once $baseDir.'/.core/'.$file;

if(!ReqPut()) ReqBad();

$req = json_decode(file_get_contents('php://input'),1);

$pgroupId = validInt(HEADERS['pgroupid']);
$groupId = validInt($req['groupId']);

$dbdata = [
    'action' => 3,
    'customerId' => $headers['customerid'],
    'pgroupId' => $pgroupId,
    'groupId' => $groupId,
    'title' => validString($req['title'])
];

try {
    // Update the group details
    $return = PROC(ContactGroup($dbdata))[0][0]; // Done

    if(isset($return['updated']) && $return['updated'] != -1){

        $filter = [
            "_id" => $groupId,
            "pgroupId" => $pgroupId
        ];

        $updates = [
            'title' => $req['title'],
            'contacts' => count($req['contacts']),
            'updatedAt' => mongodate('NOW')
        ];

        $return2 = mongoUpdate(CGROUP,$filter,$updates);

        $kafka = new KafkaHelper(KAFKA_BROKER);

        foreach(array_chunk($req['contacts'],GROUP_CHUNKS) as $chunk) {
            $contacts = [
                'contacts' => $chunk,
                'groupId' => $groupId,
                'pgroupId' => $pgroupId
            ];
            // print_r(json_encode($contacts));  echo "\n\n";
            $kafka->produceMessage(KAFKA_GROUP_CONTACTS_UPDATE,json_encode($contacts));
        }

        // Flush pending messages
        $kafka->flush();

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