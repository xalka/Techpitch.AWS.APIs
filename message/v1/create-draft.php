<?php

// prevent direct access
if (!defined('DIRECT_ACCESS')) {
    header('HTTP/1.0 400 Bad Request');
    http_response_code(400);
    exit;
}

// save into mysql db

// if $req['contactGroupId'] is not set, but contacts is set, then use contacts
// create a temporary group, that will be deleted when the message is sent
if(isset($req['contacts']) && !empty($req['contacts'])){
    print_r($req['contacts']);
    exit;
}

$dbdata = [
    'action' => 6,
    'recipients' => 0,
    'title' => $req['title'],
    'message' => $req['message'],
    'alphanumeric' => $req['alphanumeric'],
    'alphanumericId' => $req['alphanumericId'],
    'transactionId' => 1,
    'customerId' => $customerId,
    'pgroupId' => $pgroupId,
    'groups' => $req['contactGroupId'],
    'scheduled' => $req['scheduled'] ?? null,
    'typeId' => $req['typeId'],
    'mode' => $req['mode'],
    'contacts' => 0,
    'units' => 0,
    'statusId' => 1,
    'status' => 'draft'
];
// print_r($dbdata); exit;
$return = PROC(Message($dbdata))[0][0];

if(isset($return['created']) && $return['created']>0){
    // save into mongo
    // Get Sender by ID
    
    $dbdata['_id'] = $return['messageId'];
    $dbdata['status'] = 'draft';
    $dbdata['type'] = $dbdata['typeId']==1 ? 'transaction' : 'bulk';
    $dbdata['mode'] = $dbdata['mode']==0 ? 'normal' : 'custom';
    $dbdata['sent'] = 0;
    $dbdata['created'] = mongodate('NOW');
    unset($dbdata['action']);

    foreach ($dbdata as $key => $value) {
        if(is_numeric($value)) $dbdata[$key] = validInt($value);
    }        

    if(mongoInsert(CMESSAGE,$dbdata)){
        $response = [
            'status' => 200
        ];             
    } else {
        $response = [
            'status' => 400,
            'message' => "Technical problems, please try again"
        ];            
    }
} else {
    $response = [
        'status' => 400,
        'message' => "Technical problems, please try again"
    ];             
}

print_j($response);