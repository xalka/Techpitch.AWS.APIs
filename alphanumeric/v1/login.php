<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';

if(!ReqPost()) ReqBad();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);

// 2. Validate

if(isset($req['phone']) && !empty($req['phone'])){
    $filter = [
        'phone' => validPhone($req['phone']),
        // 'pcode' => validInt($req['code'])
    ];
} else {
    $filter = [
        'email' => validEmail($req['email']),
        // 'ecode' => validInt($req['code'])
    ];    
}
// $filter['activated'] = 1;
$filter['active'] = 1;

$options = [
    'projection' => [
        '_id'=>1, 'email'=>1, 'phone'=>1, 'fname'=>1, 'lname'=>1, 'active'=>1, 'pass'=>1
    ],
];

$user = mongoSelect(CUSER,$filter,$options);

if(empty($user)){
    $response = [
        'status' => 400,
        'message' => "The user doesn't exist"
    ];

} else {
    // validate password
    if(password_verify($req['password'],$user[0]->pass)){
        $user = $user[0];
        $response = [
            'status' => 200,
            'id' => $user->_id,
            'fname' => isset($user->fname) ? $user->fname : null,
            'lname' => isset($user->lname) ? $user->lname : null,
            'phone' => $user->phone,
            'email' => $user->email
        ];
    } else {
        $response = [
            'status' => 400,
            'message' => 'The credential dont match'
        ];
    }
}

print_j($response);