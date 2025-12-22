<?php

// prevent direct access
if (!defined('DIRECT_ACCESS')) {
    header('HTTP/1.0 400 Bad Request');
    http_response_code(400);
    exit;
}

// 2. Validate

// 3. Check if unique fields exists | check on the mysql procedure
// $filter = [ 'phone' => validPhone($req['phone']) ];
// $options = [ 'projection' => [ '_id'=>1 ], ];
// $customer = mongoSelect(CCUSTOMER,$filter,$options);

$code = intRand();
$typeId = 1;
$type = 'individual';

// 4. Save into mysql
$dbdata = [
    'action' => 1,
    'typeId' => $typeId,
    'fname' => $req['fname'],
    'lname' => $req['lname'],
    'phone' => validPhone($req['phone']),
    'email' => validEmail($req['email']),
    'pcode' => $code,
    'password' => passEncrype(decrypt($req['password'])),
    'roleId' => ADMIN_ROLE_ID
];

try {
    $return1 = PROC(CUSTOMER($dbdata))[0][0]; // done

    if(isset($return1['created']) && $return1['created']==0){
        $response = [
            'status' => 401,
            'message' => $return1['message']
        ];    
        if(isset($return1['phone'])) $response['errors']['phone'] = "Phone number is taken";
        if(isset($return1['email'])) $response['errors']['email'] = "Email is taken";
        print_j($response);
        exit;
    }

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
    $email_template = str_replace('[NAME]', $dbdata['fname'], $email_template);
    $email_template = str_replace('[MESSAGE]', "Thank you for creating your account! To verify your email, please use the OTP code below.", $email_template);
    $email_template = str_replace('[CODE]', $code, $email_template);
    $email_template = str_replace('[YEAR]', date('Y'), $email_template);

    // send email
    $email = [
        'recipients' => [[
            'email' => $dbdata['email'],
            'name' => $dbdata['fname'],
        ]],
        'subject' => 'TechPitch Account Verification',
        'content' => $email_template
    ];
    $headers = [];
    $emailsent = callAPI('POST',API_HOST.'email/v1/send',$headers,json_encode($email));
    
    // writeToFile('/tmp/techpitch-email.log',$email_template);   
    // writeToFile('/tmp/techpitch-email.log',$emailsent);   

    // send sms

    // create account
    $dbdata = [
        "_id" => $groupId,
        "active" => 1,
        "verified" => 0,
        "units" => 0,
        "adminId" => $customerId,
        "fname" => $dbdata['fname'],
        "lname" => $dbdata['lname'],
        "email" => $dbdata['email'],
        "phone" => $dbdata['phone'],
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

} catch (Exception $e) {
    $response = [
        'status' => 401,
        'error' => $e->getMessage()
    ];    
}



// 6. Send OTP to phone number
// Pending internal endpoint
// writeToFile('/tmp/techpitch-sms.log',json_encode($dbdata));

// 7. Respond with a json
print_j($response);