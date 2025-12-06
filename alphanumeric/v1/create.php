<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

// POST request only
if(!ReqPost()) ReqBad();

$headers = getallheaders();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'), true);

// 2. Validate

// 4. Save into mysql
$dbdata = [
    'action' => 13,
    'alphanumeric' => validString($req['alphanumeric']),
    'groupId' => validInt($req['accountId']),
    'adminId' => validInt($headers['Adminid'])
];

try {
    $return = PROC(CUSTOMER($dbdata))[0][0];

    if(isset($return['created']) && $return['created']==0){
        $response = [
            'status' => 401,
            'message' => isset($return['message']) ? $return['message'] : "Technical problem",
        ];
        print_j($response);
        exit;
    }

    if(isset($return['alphanumericId']) && $return['alphanumericId'] > 0){
        // 5. Save into mongodb

        $alphan = [
            '_id' => validInt($return['alphanumericId']),
            'title' => $return['title'],
            'cname' => $return['cname'] ?? null,
            'fname' => $return['fname'] ?? null,
            'lname' => $return['lname'] ?? null,
            'groupId' => validInt($return['groupId']),
            'customerId' => $return['customerId'],
            'active' => $return['active'] ? 'active' : 'in-active',
            'created' => mongodate($return['created']),
        ];

        $return2 = mongoInsert(CALPHANUMERIC,$alphan);

        $response = [
            'status' => 201,
            // 'type' => $dbdata['type'],
            // 'error' => 0
        ];        

    } else {
        $response = [
            'status' => 401,
            'message' => $return['message']
        ];      
    }  

} catch (Exception $e) {
    $response = [
        'status' => 401,
        'error' => $e->getMessage()
    ];    
}



// 6. Send OTP to phone number
// Pending internal endpoint
writeToFile('/tmp/techpitch-sms.log',json_encode($dbdata));

// 7. Respond with a json
print_j($response);