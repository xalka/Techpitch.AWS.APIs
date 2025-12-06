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

$filter = [
    'active' => 1
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

if(empty($user)){
    $response = [
        'status' => 400,
        'message' => 'The user doent exists'
    ];

} else {
    $user = $user[0];
    $code = intRand();
    $dbdata = [
        'action' => 3,
        'userId' => $user->_id,
        'code' => $code
    ];
    // if($customer->type='individual') $dbdata['pcode'] = $code;
    // else $dbdata['ecode'] = $code;
    // $dbdata['code'] = $code;
    
    try {
        // update mysql for password reset
        $return1 = PROC(USER($dbdata))[0][0];

        $dbdata = [ 'code' => $code, 'passreset' => 1];

        $return2 = mongoUpdate(CUSER, [ '_id' => validInt($user->_id) ], $dbdata );

        $template = file_get_contents(__dir__.'/../../_email/otp.html');
        $template = str_replace('[TITLE]', 'Forgot Password', $template);
        $template = str_replace('[LOGO]', img2base64(LOGO), $template);
        $template = str_replace('[HEADING]', 'Forgot Password', $template);
        $template = str_replace('[NAME]', $user->fname, $template);
        $template = str_replace('[MESSAGE]', "We received a request to reset your password. Use the OTP code below to verify your identity.", $template);
        $template = str_replace('[CODE]', $code, $template);
        $template = str_replace('[YEAR]', date('Y'), $template);         

        $email = [
            'recipients' => [[
                'email' => $user->email,
                'name' => $user->fname,
            ]],
            'subject' => 'TechPitch: Forgot Password Assistance',
            // 'content' => "Welcome back, your OTP is ".$code
            'content' => $template
        ];
        $headers = [];
        $emailsent = callAPI('POST',API_HOST.'email/v1/send',$headers,json_encode($email));    
        writeToFile('/tmp/techpitch-email.log',$template);   
        writeToFile('/tmp/techpitch-email.log',$emailsent);

        $response = [
            'status' => 201,
            'message' => "OTP has been sent to you"
        ];         

    } catch (Exception $e) {
        $response = [
            'status' => 401,
            'error' => $e->getMessage()
        ];    
    }        

    // update mongodb for password reset
    
    #$updated = mongoUpdate(CCUSTOMER,$filter, $update=[]);

    // send OTP 

    // validate password
    // if(password_verify($req['password'],$customer[0]->password)){
    //     $customer = $customer[0];
    //     $response = [
    //         'status' => 200,
    //         'id' => $customer->_id,
    //         'cname' => isset($customer->cname) ? $customer->cname : null,
    //         'fname' => isset($customer->fname) ? $customer->fname : null,
    //         'lname' => isset($customer->lname) ? $customer->lname : null,
    //         'phone' => $customer->phone,
    //         'email' => $customer->email,
    //         'type' => $customer->type
    //     ];
    // } else {
    //     $response = [
    //         'status' => 400,
    //         'message' => 'The credential dont match'
    //     ];
    // }
}

print_j($response);