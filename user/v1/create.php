<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

// POST request only
if(!ReqPost()) ReqBad();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);

// 2. Validate

$otp = intRand();

// 4. Save into mysql
$dbdata = [
    'action' => 1,
    'fname' => validString($req['fname']),
    'lname' => validString($req['lname']),
    'phone' => validPhone($req['phone']),
    'email' => validEmail($req['email']),
    'pcode' => $otp,
    'password' => passEncrype(decrypt($otp)),
    'passreset' => 1,
    'roleId' => validInt($req['roleId']),
    'adminId' => HEADERS['adminid']
];

try {
    $return1 = PROC(User($dbdata));

    if(!isset($return1[0][0]['created'])){
        $return1 = $return1[0][0];
        $response = [
            'status' => 401,
            'message' => $return1['message']
        ];
        if(isset($return1['phone'])) $response['errors']['phone'] = "Phone number is taken";
        if(isset($return1['email'])) $response['errors']['email'] = "Email is taken";
        print_j($response);
        exit;
    }

    if(isset($return1[0][0]['userId']) && $return1[0][0]['userId'] > 1){

        $return1 = $return1[0][0];
        
        $userId = $return1['userId'];

        unset($dbdata['action']);

        $dbdata['_id'] = $userId;
        $dbdata['created'] = mongodate('NOW');
        $dbdata['role'] = $return1['title'];
        $dbdata['active'] = 1;

        // print_r($dbdata);

        $return2 = mongoInsert(CUSER,$dbdata);

        // print_r($return2);

        if($return2){

            // send sms
            $sms = "Welcome to TechPitch, your OTP is $otp";

            // template
            $email_template = file_get_contents(__dir__.'/../../_email/otp.html'); 
            $email_template = str_replace('[TITLE]', 'OTP', $email_template);
            $email_template = str_replace('[LOGO]', img2base64(LOGO), $email_template);
            $email_template = str_replace('[HEADING]', 'OTP', $email_template);
            $email_template = str_replace('[NAME]', $dbdata['fname'], $email_template);
            $email_template = str_replace('[MESSAGE]', "Welcome to Techpitch, your OTP is .", $email_template);
            $email_template = str_replace('[CODE]', $code, $email_template);
            $email_template = str_replace('[YEAR]', date('Y'), $email_template);

            // send email
            $email = [
                'recipients' => [[
                    'email' => $dbdata['email'],
                    'name' => $dbdata['fname'],
                ]],
                'subject' => 'TechPitch OTP',
                'content' => $email_template
            ];
            $headers = [];
            //$emailsent = callAPI('POST',API_HOST.'email/v1/send',$headers,json_encode($email));        

            $response = [
                'status' => 201,
                'error' => 0
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