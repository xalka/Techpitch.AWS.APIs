<?php

// prevent direct access
if (!defined('DIRECT_ACCESS')) {
    header('HTTP/1.0 400 Bad Request');
    http_response_code(400);
    exit;
}

// 1. Receive json
// print_r($req);

// 2. Validate

// 3. Check if unique fields exists

$code = intRand();
$typeId = 2;
$type = 'corporate';

// 4. Save into mysql
$dbdata = [
    'action' => 1,
    'typeId' => $typeId,
    'cname' => validString($req['cname']),
    'email' => validEmail($req['email']),
    'pcode' => $code,
    'password' => passEncrype(decrypt($req['password'])),
    'roleId' => ADMIN_ROLE_ID
];

try {
    $return1 = PROC(CUSTOMER($dbdata))[0][0];

    if($return1['created']){

        // 5. Save into mongodb
        $groupId = validInt($return1['groupId']);
        $customerId = validInt($return1['id']);

        unset($dbdata['action']);
        $dbdata['_id'] = $customerId;
        $dbdata['groupId'] = $groupId;
        $dbdata['groups'][] = $groupId;
        $dbdata['created'] = mongodate('NOW');
        $dbdata['type'] = $type;
        $dbdata['roleId'] = (int)$return1['roleId'];
        $dbdata['role'] = $return1['title'];    
        $dbdata['active'] = 1;
        $dbdata['verified'] = 0;        

        $return2 = mongoInsert(CCUSTOMER,$dbdata);

        $email_template = file_get_contents(__dir__.'/../../_email/otp.html');
        $email_template = str_replace('[TITLE]', 'Verification Code', $email_template);
        $email_template = str_replace('[LOGO]', img2base64(LOGO), $email_template);
        $email_template = str_replace('[HEADING]', 'Verification Code', $email_template);
        $email_template = str_replace('[NAME]', $dbdata['cname'], $email_template);
        $email_template = str_replace('[MESSAGE]', "Thank you for creating your account! To verify your email, please use the OTP code below.", $email_template);
        $email_template = str_replace('[CODE]', $code, $email_template);
        $email_template = str_replace('[YEAR]', date('Y'), $email_template);        
    
        // send email
        $email = [
            'recipients' => [[
                'email' => $dbdata['email'],
                'name' => $dbdata['cname'],
            ]],
            'subject' => 'TechPitch Account Verification',
            'content' => $email_template
        ];
        $headers = [];
        $emailsent = callAPI('POST',API_HOST.'email/v1/send',$headers,json_encode($email));
        writeToFile('/tmp/techpitch-email.log',$email_template);   
        writeToFile('/tmp/techpitch-email.log',$emailsent);   
        
        // create account
        $dbdata = [
            "_id" => $groupId,
            "active" => 1,
            "verified" => 0,
            "units" => 0,
            "adminId" => $customerId,
            "cname" => $dbdata['cname'],
            "email" => $dbdata['email'],
            "type" => $type,
            "created" => mongodate('NOW')
        ];
        $return3 = mongoInsert(CACCOUNT,$dbdata);        

        if($return2 && $return3){
            $response = [
                'status' => 201,
                'type' => $type,
                'error' => 0
            ];
        }
        
    } else {
        // 5. Save into mongodb
        $response = [
            'status' => 401,
            'error' => $return1['message']
        ];
    }

} catch (Exception $e) {
    $response = [
        'status' => 401,
        'error' => $e->getMessage()
    ];    
}

// 6. Send OTP to email number
// Pending internal endpoint
writeToFile('/tmp/techpitch-email.log',json_encode($dbdata));

// 7. Respond with a json
print_j($response);