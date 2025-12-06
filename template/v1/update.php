<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

// PUT request only
if(!ReqPut()) ReqBad();

$headers = getallheaders();

if(!isset($headers['Groupid'])) ReqBad();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);

// 2. Validate

// 3. Check if title is exists
$dbdata = [
    'action' => 3,
    'id' => $req['id'],
    'customerId' => $headers['Customerid'],
    'groupId' => $headers['Groupid'],
    'title' => $req['title'],
    'message' => $req['message']
];

try {
    // 4. Save into mysql
    $return = PROC(Template($dbdata))[0][0];

    if($return){

        $filter = [
            '_id' => (int)$dbdata['id'],
            'groupId' => (int)$dbdata['groupId']
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