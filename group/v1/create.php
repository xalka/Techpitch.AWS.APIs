<?php

$baseDir = dirname(__dir__,2);

require_once $baseDir.'/.config/.config.php'; 
$files = ['.funcs.php','.mysql.php','.mongodb.php','.procedures.php','KafkaHelper.php'];
foreach ($files as $file) require_once $baseDir.'/.core/'.$file;

if(!ReqPost()) ReqBad();

$req = json_decode(file_get_contents('php://input'),1);

$pgroupId = validInt(HEADERS['pgroupid']);

$dbdata = [
    'action' => 1,
    'customerId' => HEADERS['customerid'],
    'pgroupId' => $pgroupId,
    'title' => validString($req['title'])
];

try {
    // 4. Save into mysql
    $return = PROC(ContactGroup($dbdata))[0][0]; // Done

    if(isset($return['created']) && $return['created'] > 0){
        $pgroupId = $pgroupId;
        $groupId = $return['groupId'];

        $group = [
            '_id' => (int)$groupId,
            'pgroupId' => $pgroupId,
            'title' => $req['title'],
            'contacts' => count($req['contacts']),
            'active' => 1,
            'created' => mongodate('NOW')
        ];

        $return2 = mongoInsert(CGROUP,$group);

        $kafka = new KafkaHelper(KAFKA_BROKER);

        foreach(array_chunk($req['contacts'],GROUP_CHUNKS) as $chunk) {
            $contacts = [
                'contacts' => $chunk,
                'groupId' => $groupId,
                'pgroupId' => $pgroupId
            ];
            $kafka->produceMessage(KAFKA_GROUP_CONTACTS_CREATE,json_encode($contacts));
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