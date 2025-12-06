<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
// require __dir__.'/../../.config/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

// POST request only
if(!ReqPost()) ReqBad();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);

$headers = getallheaders();

switch($req['action']){
    case 1:
        $dbdata = [
            'action' => 3,
            'amount' => $req['amount'],
            'reference' => $req['MpesaReceiptNumber'],
            'MerchantRequestID' => $req['MerchantRequestID'],
            'CheckoutRequestID' => $req['CheckoutRequestID'],
            'phone' => $req['PhoneNumber'],
            'thirdpartyTime' => date('Y-m-d H:i:s',strtotime($req['TransactionDate'])),
            'description' => $req['ResultDesc'],
            'statusId' => 3
        ];      
        break;

    case 2:
        $dbdata = [
            'action' => 4,
            'paymentId' => $req['BillRefNumber'],
            'reference' => $req['TransID'], 
            'amount' => $req['TransAmount'],
            'fname' => $req['FirstName'],
            // 'mname' => $req['MiddleName'],
            // 'lname' => $req['LastName'],
            // 'phone' => $req['MSISDN'],
            'thirdpartyTime' => date('Y-m-d H:i:s',strtotime($req['TransTime'])),
            'statusId' => 3
        ];   
        break;

    case 3:
        $dbdata = [
            'action' => 10,
            'MerchantRequestID' => $req['MerchantRequestID'],
            'CheckoutRequestID' => $req['CheckoutRequestID'],
            'description' => $req['ResultDesc'],
            'statusId' => $req['statusId']
        ];   
        break;
    
    default:
        ReqBad();
        break;
}

try {
    // 2. update database with the callback, inside the procedure, calculate number of SMS
    // print_j($dbdata); exit;
    $response = PROC(Payment($dbdata));

    if(isset($response[0][0]['updated']) && $response[0][0]['updated'] == 1){
        $response = [
            'status' => 201
        ];
    } else {
        $response = [
            'status' => 401,
            'message' => $response[0][0]['message'] ?? null
        ];
    }    

} catch (Exception $e) {
    $response = [
        'status' => 401,
        'error' => $e->getMessage()
    ];    
}

// 7. Respond with a json
print_j($response);