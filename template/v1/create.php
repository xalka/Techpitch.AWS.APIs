<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php';

// POST request only
if(!ReqPost()) ReqBad();

if(!isset(HEADERS['pgroupid']) || empty(HEADERS['pgroupid'])) ReqBad();

$pgroupId = validInt(HEADERS['pgroupid']);

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);

// 2. Validate

// 3. Check if title is exists
$dbdata = [
    'action' => 1,
    'customerId' => HEADERS['customerid'],
    'pgroupId' => $pgroupId,
    'title' => validString($req['title']),
    'message' => validString($req['message'])
];

try {
    // 4. Save into mysql
    $return = PROC(Template($dbdata));

    if(isset($return[0][0]['created']) && $return[0][0]['created']>0){
        $return = $return[0][0];
        $template = [
            '_id' => (int)$return['id'],
            'title' => $dbdata['title'],
            'message' => $dbdata['message'],
            'strlen' => (int)strlen($dbdata['message']),
            'groupId' => $pgroupId,
            'created' => mongodate('NOW')
        ];

        $return2 = mongoInsert(CTEMPLATE,$template);

        if($return2){
            $response = [
                'status' => 201
            ];
        } else {
            $response = [
                'status' => 401,
                'message' => 'Technical error'
            ];               
        }

    } else {
        $response = [
            'status' => 401,
            'message' => $return[0][0]['message']
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