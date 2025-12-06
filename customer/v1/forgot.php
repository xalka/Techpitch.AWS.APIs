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
    // 'verified' => 1,
    'active' => 1
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

$customer = mongoSelect(CCUSTOMER,$filter,$options); // print_r($customer); exit;

if(empty($customer)){
    $response = [
        'status' => 400,
        'message' => 'The customer doent exists'
    ];

} else {
    $customer = $customer[0];
    $code = intRand();
    $dbdata = [
        'action' => 3,
        'customerId' => $customer->_id,
        'pcode' => $code
    ];
    // if($customer->type='individual') $dbdata['pcode'] = $code;
    // else $dbdata['ecode'] = $code;
    // $dbdata['code'] = $code;
    
    try {
        // update mysql for password reset
        $return1 = PROC(CUSTOMER($dbdata))[0][0];

        $dbdata = [ 'pcode' => $code, 'passreset' => 1];

        $return2 = mongoUpdate(CCUSTOMER, [ '_id' => validInt($customer->_id) ], $dbdata );

        $email_template = file_get_contents(__dir__.'/../../_email/otp.html');
        $email_template = str_replace('[TITLE]', 'Forgot Password', $email_template);
        $email_template = str_replace('[LOGO]', img2base64(LOGO), $email_template);
        $email_template = str_replace('[HEADING]', 'Forgot Password', $email_template);
        $email_template = str_replace('[NAME]', $customer->fname, $email_template);
        $email_template = str_replace('[MESSAGE]', "We received a request to reset your password. Use the OTP code below to verify your identity.", $email_template);
        $email_template = str_replace('[CODE]', $code, $email_template);
        $email_template = str_replace('[YEAR]', date('Y'), $email_template);         

        $email = [
            'recipients' => [[
                'email' => $customer->email,
                'name' => $customer->fname,
            ]],
            'subject' => 'TechPitch: Forgot Password Assistance',
            // 'content' => "Welcome back, your OTP is ".$code
            'content' => $email_template
        ];
        $headers = [];
        $emailsent = callAPI('POST',API_HOST.'email/v1/send',$headers,json_encode($email));    
        writeToFile('/tmp/techpitch-email.log',$email_template);   
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