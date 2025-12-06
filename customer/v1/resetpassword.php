<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

if(!ReqPost()) ReqBad();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1); // print_j($req['phone']); exit;

// 2. Validate

$code = validInt($req['code']);

$filter = [
    // 'verified' => 1,
    'active' => 1,
    'passreset' => 1,
    'pcode' => $code
];

if(isset($req['phone']) && !empty($req['phone'])){
    $filter['phone'] = validPhone($req['phone']);
} else {
    $filter['email'] = new MongoDB\BSON\Regex('^'.validEmail($req['email']).'$', 'i');
}

$options = [
    'projection' => [
        '_id'=>1, 'email'=>1, 'phone'=>1, 'cname'=>1, 'fname'=>1, 'lname'=>1, 'type'=>1
    ],
];

$customer = mongoSelect(CCUSTOMER,$filter,$options);

if(!empty($customer)){
    $customer = $customer[0];

    try {
        // update mysql
        $dbdata = [
            'action' => 4,
            'customerId' => $customer->_id,
            'passreset' => 1,
            'pcode' => $code,
            'password' => passEncrype($req['password'])
        ];
        $return1 = PROC(CUSTOMER($dbdata));
        // print_j($return1);

        // update mongodb
        unset($dbdata['action']);
        unset($dbdata['customerId']);
        $dbdata['passreset'] = 0;

        // if(isset($req['phone'])) $dbdata['pcode'] = $code;
        // else $dbdata['pcode'] = $code;

        $return2 = mongoUpdate(CCUSTOMER, ['_id'=>$customer->_id], $dbdata);  
        // print_j($return2);
        
        $response = [
            'status' => 201,
            'url' => "login"
        ];          

    } catch (Exception $e) {
        $response = [
            'status' => 401,
            'message' => $e->getMessage()
        ];    
    }  
} else {
    $response = [
        'status' => 401,
        'message' => 'Invalid code'
    ];
}

print_j($response);