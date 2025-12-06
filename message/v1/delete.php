<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

// PUT request only
if(!ReqDelete()) ReqBad();

$headers = getallheaders();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);
// parse_str(file_get_contents("php://input"), $req);
// print_r($req); exit;

// 2. Validate

// 3. Check if title is exists
$dbdata = [
    'action' => 7,
    'messageId' => validInt($req['id']),
    'customerId' => validInt($headers['Customerid']),
    'pgroupId' => validInt($headers['Groupid'])
];
// print_r($dbdata); exit;
try {
    // 4. Save into mysql
    $return = PROC(Message($dbdata))[0][0];

    if($return['deleted']){

        $filter = [
            '_id' => (int)$dbdata['messageId'],
            'pgroupId' => (int)$dbdata['pgroupId']
        ];
        $return2 = mongoDelete(CMESSAGE,$filter);
        // print_r($return2);

        if($return2){
            $response = [
                'status' => 201
            ];
        };

    } else {
        $response = [
            'status' => 200,
            'message' => "unable to delete message"
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