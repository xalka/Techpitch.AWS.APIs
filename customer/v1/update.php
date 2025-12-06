<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

// PUT request only
if(!ReqPut()) ReqBad();

$headers = getallheaders();

$req = json_decode(file_get_contents('php://input'),1);

// Validate

// 4. Save into mysql
$customerId = validInt($req['id']);
$dbdata = [
    'action' => 11,
    'customerId' => $customerId,
    'fname' => $req['fname'],
    'lname' => $req['lname'],
    'phone' => validPhone($req['phone']),
    'email' => validEmail($req['email']),
    'roleId' => validInt($req['roleId']),
    'groupId' => $headers['Groupid'],
    'adminId' => $headers['Customerid']
];

try {
    $return1 = PROC(CUSTOMER($dbdata))[0][0];

    if(isset($return1['updated']) && $return1['updated']==-1){
        $response = [
            'status' => 401,
            'message' => isset($return1['message']) ? $return1['message'] : 'Technical problem, please try again'
        ];    
        if(isset($return1['phone'])) $response['errors']['phone'] = "Phone number is taken";
        if(isset($return1['email'])) $response['errors']['email'] = "Email is taken";

    } elseif(isset($return1['updated']) && $return1['updated']==0){
        $response = [
            'status' => 401,
            'message' => "No changes to update"
        ];         
    
    } else {

        // 5. update mongodb
        $dbdata2 = [
            'action' => 6,
            'customerId' => $customerId
        ];
        $roles = PROC(CUSTOMER($dbdata2))[0];

        $filter = [
            '_id' => $customerId
        ];
        $update = [
            'updatedAt' => mongodate('NOW'),
            'updatedBy' => validInt($dbdata['adminId']),
            'groups' => array_column($roles,'groupId'),
            'roles' => $roles
        ];

        if(mongoUpdate(CCUSTOMER,$filter,$update)){
            $response = [
                'status' => 201
            ];          
        }
    }

} catch (Exception $e) {
    $response = [
        'status' => 401,
        'error' => $e->getMessage()
    ];    
}

// 7. Respond with a json
print_j($response);