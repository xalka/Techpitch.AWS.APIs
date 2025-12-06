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
    'active' => 1,
    'passreset' => 1,
    'code' => $code
];

if(isset($req['phone']) && !empty($req['phone'])){
    $filter['phone'] = validPhone($req['phone']);
} else {
    $filter['email'] = new MongoDB\BSON\Regex('^'.validEmail($req['email']).'$', 'i');
}

$options = [
    'projection' => [
        '_id'=>1, 'email'=>1, 'phone'=>1, 'fname'=>1, 'lname'=>1
    ],
];

$user = mongoSelect(CUSER,$filter,$options);

if(!empty($user)){
    $user = $user[0];

    try {
        // update mysql
        $dbdata = [
            'action' => 4,
            'userId' => $user->_id,
            'passreset' => 1,
            'code' => $code,
            'password' => passEncrype($req['password'])
        ];
        $return1 = PROC(USER($dbdata))[0][0];
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
            // update mongodb
            $dbdata = [
                'passreset' => 0,
                'code' => null,
                'pass' => $dbdata['password'],
                'updated' => mongodate('NOW'),
            ];

            // if(isset($req['phone'])) $dbdata['pcode'] = $code;
            // else $dbdata['pcode'] = $code;

            $return2 = mongoUpdate(CUSER, ['_id'=>$user->_id], $dbdata);
            // print_j($return2);
            
            $response = [
                'status' => 201,
                'url' => "login"
            ];  

        }        

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