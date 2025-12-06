<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

// PUT request only
if(!ReqDelete()) ReqBad();

$headers = getallheaders();

if(!isset($headers['Groupid'])) ReqBad();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);
// parse_str(file_get_contents("php://input"), $req);

// 2. Validate

// 3. Check if title is exists
$dbdata = [
    'action' => 4,
    'id' => validInt($req['id']),
    'customerId' => validInt($headers['Customerid']),
    'groupId' => validInt($headers['Groupid'])
];

try {
    // 4. Save into mysql
    $return = PROC(Template($dbdata))[0][0];

    if($return['deleted']){

        $filter = [
            '_id' => (int)$dbdata['id'],
            'groupId' => (int)$dbdata['groupId']
        ];
        $return2 = mongoDelete(CTEMPLATE,$filter);
        // print_r($return2);

        if($return2){
            $response = [
                'status' => 201
            ];
        };

    } else {
        $response = [
            'status' => 200,
            'message' => "unable to delete template"
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