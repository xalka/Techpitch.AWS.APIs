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
$customerId = validInt($req['customerId']);

// authorize 
$filter = [
    'active' => 1,
    'verified' => 1,
    '_id' => $customerId
];

// if(isset($req['phone']) && !empty($req['phone'])) $filter['phone'] = validPhone($req['phone']);
// else $filter['email'] = new MongoDB\BSON\Regex('^'.validEmail($req['email']).'$', 'i'); // validEmail($req['email']);

$options = [
    'projection' => [
        '_id'=>1, 'password'=>1 // , 'phone'=>1, 'email'=>1
    ],
];

try {

    $customer = mongoSelect(CCUSTOMER,$filter,$options);

    if(empty($customer)){
        $response = [
            'status' => 400,
            'message' => 'The customer doent exists'
        ];

    } else {
        // validate password
        if(password_verify($req['password'],$customer[0]->password)){

            if($_GET['action'] == 'profile'):

                // profile
                $dbdata = [
                    'action' => 14,
                    'customerId' => $customerId,
                    'fname' => validString($req['fname']),
                    'lname' => validString($req['lname']),
                    'phone' => validPhone($req['phone']),
                    'email' => validEmail($req['email']),
                    'img' => isset($req['img']) ? validString($req['img']) : null,
                    'address' => $req['address'] ? addslashes($req['address']) : null
                ];

                $return1 = PROC(CUSTOMER($dbdata))[0][0]; 
                if(isset($return1['updated']) && $return1['updated']==-1){
                    $response = [
                        'status' => 401,
                        'message' => $return1['message']
                    ];
                    exit(print_j($response));

                } elseif(isset($return1['updated']) && $return1['updated']==0){
                    $response = [
                        'status' => 401,
                        'message' => "No changes to update"
                    ];
                    exit(print_j($response));
                }

                // update mongodb
                $filter = [ '_id' => $customerId ];
                unset($dbdata['action'],$dbdata['customerId']);
                if(empty($dbdata['img'])) unset($dbdata['img']);

                $return2 = mongoUpdate(CCUSTOMER,$filter,$dbdata);

                if($return2){
                    $response = [
                        'status' => 200,
                        'message' => 'Profile updated'
                    ];
                } else {
                    $response = [
                        'status' => 400,
                        'message' => 'Failed to update profile'
                    ];
                }
                exit(print_j($response));


            // security
            elseif($_GET['action'] == 'security'):

                print_r($_POST);

            endif;

        } else {
            $response = [
                'status' => 400,
                'message' => 'The credential dont match'
            ];
            exit(print_j($response));
        }
    }

} catch (Exception $e) {
    $response = [
        'status' => 401,
        'error' => $e->getMessage()
    ];    
    exit(print_j($response));
}