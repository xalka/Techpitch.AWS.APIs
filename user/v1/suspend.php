<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

// PUT request only
if(!ReqDelete()) ReqBad();

$headers = getallheaders();

$req = json_decode(file_get_contents('php://input'),1);

// Validate

// 4. Update mysql
$customerId = validInt($req['id']);
$dbdata = [
    'action' => 12,
    'customerId' => $customerId,
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

    } elseif(isset($return1['updated']) && $return1['updated']==0){
        $response = [
            'status' => 401,
            'message' => "No changes to update"
        ];         
    
    } else {

        // 5. update mongodb
        $filter = [
            '_id' => $customerId
        ];
        $dbdata = [
            'active' => 0,
            'updated' => mongodate('NOW'),
            'suspendedBy' => validInt($dbdata['adminId'])
        ];
        if(mongoUpdate(CCUSTOMER,$filter,$dbdata)){
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