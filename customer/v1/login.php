<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
// require __dir__.'/../../.config/.mysql.php'; 
require __dir__.'/../../.core/.mongodb.php';
// require __dir__.'/../../.config/.procedures.php';

if(!ReqPost()) ReqBad();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1); // print_j($req); exit;

// 2. Validate

$filter = [
    'active' => 1,
    'verified' => 1
];

if(isset($req['phone']) && !empty($req['phone'])) $filter['phone'] = validPhone($req['phone']);
else $filter['email'] = new MongoDB\BSON\Regex('^'.validEmail($req['email']).'$', 'i'); // validEmail($req['email']);

$options = [
    'projection' => [
        '_id'=>1, 'email'=>1, 'phone'=>1, 'cname'=>1, 'fname'=>1, 'lname'=>1, 'type'=>1, 'password'=>1, 'groupId'=>1, 'groups'=>1, 'roleId'=>1
    ],
];

$customer = mongoSelect(CCUSTOMER,$filter,$options);

if(empty($customer)){
    $response = [
        'status' => 400,
        'message' => "Oops! We couldn't find that customer. Please check your details and try again."
    ];

} else {
    // validate password
    if(password_verify($req['password'],$customer[0]->password)){
        $customer = $customer[0];
        $response = [
            'status' => 200,
            'id' => $customer->_id,
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
    } else {
        $response = [
            'status' => 400,
            'message' => 'The credential dont match'
        ];
    }
}

print_j($response);