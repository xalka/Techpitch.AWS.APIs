<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

// PUT request only
if(!ReqPut()) ReqBad();

if(!isset(HEADERS['pgroupid'])) ReqBad();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);

// 2. Validate

$pgroupId = HEADERS['pgroupid'];
$id = validInt($req['id']);

// 3. Check if title is exists
$dbdata = [
    'action' => 3,
    'id' => $id,
    'customerId' => HEADERS['customerid'],
    'pgroupId' => $pgroupId,
    'title' => validString($req['title']),
    'message' => validString($req['message'])
];
// print_r($dbdata); exit;
try {
    // 4. Save into mysql
    $return = PROC(Template($dbdata));
    
    if(isset($return[0][0]['updated']) && $return[0][0]['updated'] !=-1){

        $filter = [
            '_id' => (int)$id,
            'groupId' => (int)$pgroupId
        ];

        $template = [
            'title' => $dbdata['title'],
            'message' => $dbdata['message'],
            'strlen' => (int)strlen($dbdata['message']),
            'modified' => mongodate('NOW')
        ];

        $return2 = mongoUpdate(CTEMPLATE,$filter,$template);         

        if($return2){
            $response = [
                'status' => 201
            ];
        }    

    } else {
        $response = [
            'status' => 200,
            'message' => "No changes made"
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