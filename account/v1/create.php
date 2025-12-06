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
$req = json_decode(file_get_contents('php://input'),1); 
print_r($req); exit;
// 2. Validate

$otp = intRand();

// 4. Save into mysql
$dbdata = [
    'action' => 5,
    'typeId' => 1,
    'fname' => $req['fname'],
    'lname' => $req['lname'],
    'phone' => validPhone($req['phone']),
    'email' => validEmail($req['email']),
    'pcode' => $otp,
    'password' => passEncrype(decrypt($otp)),
    'passreset' => 1,
    'roleId' => $req['roleId'],
    'groupId' => $headers['Groupid'],
    'adminId' => $headers['Customerid']
];

try {
    $return1 = PROC(CUSTOMER($dbdata))[0][0];

    if(isset($return1['created']) && $return1['created']==0){
        $response = [
            'status' => 401,
            'message' => $return1['message']
        ];
        if(isset($return1['phone'])) $response['errors']['phone'] = "Phone number is taken";
        if(isset($return1['email'])) $response['errors']['email'] = "Email is taken";
        print_j($response);
        exit;
    }

    $customerId = $return1['id'];

    if($return1['newly']==1){
        // 5. Save into mongodb
        unset($dbdata['action']);
        $dbdata['_id'] = $customerId;
        $dbdata['groupId'] = (int)$return1['groupId'];
        $dbdata['groups'] = [(int)$return1['groupId']];
        $dbdata['roleId'] = (int)$return1['roleId'];
        $dbdata['role'] = $return1['title'];
        $dbdata['created'] = mongodate('NOW');
        $dbdata['type'] = 'individual';
        $dbdata['active'] = 1;
        $dbdata['activated'] = 0;

        $return2 = mongoInsert(CCUSTOMER,$dbdata);
    
    } else {
        // get customer groups
        $dbdata2 = [
            'action' => 6,
            'customerId' => $customerId
        ];
        $groups = PROC(CUSTOMER($dbdata2))[0];

        // update
        $filter = [ '_id' => $customerId ];
        $update = [ 
            'phone' => $dbdata['phone'],
            'email' => $dbdata['email'],
            // 'groupId' => implode(',', array_column($groups,'groupId')) 
            'groups' => array_column($groups,'groupId')
        ];
        $return2 = mongoUpdate(CCUSTOMER,$filter,$update);
    }

    if($return2){

        // send sms
        $sms = "Welcome to TechPitch, your OTP is $otp";

        $response = [
            'status' => 201,
            // 'type' => $dbdata['type'],
            'error' => 0
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