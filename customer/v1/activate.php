<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

if(!ReqPost()) ReqBad();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);

// 2. Validate
if(isset($req['phone']) && !empty($req['phone'])){
    $filter = [
        'phone' => validPhone($req['phone'])
    ];
} else {
    $filter = [
        'email' => validEmail($req['email'])
    ];    
}
$filter['verified'] = 0; 
$filter['pcode'] = validInt($req['code']);

$options = [
    'projection' => [
        '_id'=>1, 'email'=>1, 'phone'=>1, 'pcode'=>1, 'fname'=>1, 'cname'=>1, 'lname'=>1, 'type'=>1, 'groupId'=>1, 'groups'=>1, 'roleId'=>1
    ],
];

// 3. Check if unique fields exists
$customer = mongoSelect(CCUSTOMER,$filter,$options);
if(!empty($customer)){
    $customer = $customer[0];
    try {
        // update mysql
        $dbdata = array_merge($filter,[
            'action' => 2,
            'vtype' => isset($filter['phone']) ? 'phone' : 'email'
        ]);
        $return1 = PROC(CUSTOMER($dbdata))[0][0];

        $customerId = validInt($customer->_id);

        // update mongo
        $filter = ['_id' => $customerId];
        $return2 = mongoUpdate(CCUSTOMER,$filter,['verified'=>1]);

        $return3 = mongoUpdate(CACCOUNT,['_id'=>validInt($customer->groupId)],['verified'=>1]);
        
        $response = [
            'status' => 200,
            'id' => $customerId,
            'cname' => isset($customer->cname) ? $customer->cname : null,
            'fname' => isset($customer->fname) ? $customer->fname : null,
            'lname' => isset($customer->lname) ? $customer->lname : null,
            'phone' => isset($customer->phone) ? $customer->phone : null,
            'email' => isset($customer->email) ? $customer->email : null,
            'groupId' => $customer->groupId,
            'type' => $customer->type,
            'roleId' => $customer->roleId,
            'groups' => implode(',',$customer->groups)
        ];        
        
    } catch (Exception $e){
        $response = [
            'status' => 401,
            'message' => $e->getMessage()
        ];
    }

} else {
    $response = [
        'status' => 401,
        'message' => 'Invalid activation code'
    ];
}

print_j($response);

// 4. Update mysql

// 5. Update mongodb

// 6. Send Email & SMS notifying the customer

// 7. Respond with a json