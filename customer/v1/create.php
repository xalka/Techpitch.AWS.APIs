<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

// POST request only
if(!ReqPost()) ReqBad();

$headers = getallheaders();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1); 

// 2. Validate

$otp = intRand();

// 4. Save into mysql
$dbdata = [
    'action' => 5,
    'typeId' => 1,
    'fname' => $req['fname'],
    'lname' => $req['lname'],
    'phone' => validPhone($req['phone']),
    'email' => validEmail($req['email']),
    'pcode' => $otp,
    'password' => passEncrype(decrypt($otp)),
    'passreset' => 1,
    'roleId' => $req['roleId'],
    'groupId' => $headers['Groupid'],
    'customerId' => $headers['Customerid']
];

try {
    $return1 = PROC(CUSTOMER($dbdata))[0][0];

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

    $customerId = $return1['id'];

    if($return1['newly'] == 1){
        // 5. Save into mongodb
        unset($dbdata['action']);
        $groupId = validInt($return1['groupId']);

        $dbdata['_id'] = $customerId;
        $dbdata['groupId'] = $groupId;
        $dbdata['groups'] = [$groupId];
        $dbdata['roleId'] = validInt($return1['roleId']);
        $dbdata['adminId'] = validInt($dbdata['customerId']);
        $dbdata['role'] = $return1['title'];
        $dbdata['roles'][] = [
            'groupId' => $groupId, 
            'roleId'=> $dbdata['roleId'],
            'role'=> $return1['title']
        ];
        $dbdata['created'] = mongodate('NOW');
        $dbdata['type'] = 'individual';
        $dbdata['active'] = 1;
        $dbdata['verified'] = 0;

        $return2 = mongoInsert(CCUSTOMER,$dbdata);

        $email_template = file_get_contents(__dir__.'/../../_email/otp.html');
        $email_template = str_replace('[TITLE]', 'O T P', $email_template);
        $email_template = str_replace('[LOGO]', img2base64(LOGO), $email_template);
        $email_template = str_replace('[HEADING]', 'One Time Password', $email_template);
        $email_template = str_replace('[NAME]', $dbdata['fname'], $email_template);
        $email_template = str_replace('[MESSAGE]', "Welcome to the TechPitch SMS system. Your one-time password (OTP) is provided below.", $email_template);
        $email_template = str_replace('[CODE]', $otp, $email_template);
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
        $emailsent = callAPI('POST',API_HOST.'email/v1/send',$headers,$email);
        writeToFile('/tmp/techpitch-email.log',$email_template);   
        writeToFile('/tmp/techpitch-email.log',$emailsent); 

        $response = [
            'status' => 201
        ];        
    
    } else {
        // get customer groups
        $dbdata2 = [
            'action' => 6,
            'customerId' => $customerId
        ];
        $roles = PROC(CUSTOMER($dbdata2))[0];

        // update
        $filter = [ '_id' => validInt($customerId) ];
        $update = [ 
            // 'phone' => $dbdata['phone'],
            // 'email' => $dbdata['email'],
            // 'groupId' => implode(',', array_column($groups,'groupId')) 
            'groups' => array_column($roles,'groupId'),
            'roles' => $roles
        ];
        $return2 = mongoUpdate(CCUSTOMER,$filter,$update);

        // Send email notifying the customer on the change
        $email_template = file_get_contents(__dir__.'/../../_email/notification.html');
        $email_template = str_replace('[TITLE]', 'Notificatione', $email_template);
        $email_template = str_replace('[HEADING]', 'Access Granted to an Additional Account', $email_template);
        $email_template = str_replace('[NAME]', $dbdata['fname'], $email_template);
        $email_template = str_replace('[MESSAGE]', "You have been granted access to an additional account under your existing Techpitch profile. Please log in to your account to view and manage this new access.", $email_template);
        $email_template = str_replace('[YEAR]', date('Y'), $email_template);
    
        // send email
        $email = [
            'recipients' => [[
                'email' => $dbdata['email'],
                'name' => $dbdata['fname'],
            ]],
            'subject' => 'TechPitch Account Notification',
            'content' => $email_template
        ];
        $headers = [];
        $emailsent = callAPI('POST',API_HOST.'email/v1/send',$headers,$email);
        writeToFile('/tmp/techpitch-email.log',$email_template);   
        writeToFile('/tmp/techpitch-email.log',$emailsent);         

        $response = [
            'status' => 201
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
writeToFile('/tmp/techpitch-sms.log',json_encode($dbdata));

// 7. Respond with a json
print_j($response);