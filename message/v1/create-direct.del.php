<?php

require_once __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require_once __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.redis.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

// function SaveMessage($payload){
//     // 1. save into database
//     $dbdata = [
//         'action' => 1,
//         'recepients' => count($payload['contacts']),
//         'title' => $payload['title'],
//         'message' => $payload['message'],
//         'alphanumeric' => $payload['alphanumeric'],
//         'alphanumericId' => $payload['alphanumericId'],
//         'transactionId' => $payload['transactionId'],
//         'customerId' => $payload['customerId'],
//         'pgroupId' => $payload['pgroupId'],
//         'units' => $payload['units'],
//         'statusId' => $payload['statusId'],
//         'sent' => 0
//     ];
    
//     if( $payload['method'] == 'transaction' && count($payload['contacts'])==1) $dbdata['typeId'] = 1;
//     else $dbdata['typeId'] = 2;

//     if($payload['statusId']==2){
//         $dbdata['scheduled'] = date('Y-m-d H:i:s',strtotime($payload['scheduled']));
//         $dbdata['status'] = 'scheduled';
//     } else {
//         // unset($payload['scheduled']);
//         $dbdata['status'] = 'queued';
//     }  
    
//     $return = PROC(Message($dbdata))[0][0];

//     // 2. save into mongodb
//     if(isset($return['created']) && $return['created']>0){
//         $dbdata['_id'] = $return['messageId'];
//         $dbdata['method'] = $payload['method'];
//         $dbdata['contacts'] = $payload['contacts'];
//         $dbdata['sent'] = 0;
//         $dbdata['created'] = mongodate('NOW');
//         unset($dbdata['action']);
    
//         foreach ($dbdata as $key => $value) {
//             if(is_numeric($value)) $dbdata[$key] = validInt($value);
//         }        
//         mongoInsert(CMESSAGE,$dbdata);
//     }
    
//     return $return;
// }

// function bulkSDP($payload){
//     $token = json_decode(redisGetValue(SDP_TOKEN),1);
//     $headers = [
//         'Content-Type: application/json',
//         'Accept: application/json',
//         'X-Requested-With: XMLHttpRequest',
//         'X-Authorization: Bearer '.$token
//     ];
//     $request = [
//         "timeStamp" => SDP_TIMESTAMP,
//         "dataSet" => [
//             [
//                 "userName" => SDP_USERNAME2,
//                 "channel" => "SMS",
//                 "packageId" => SDP_PACKAGE_ID, // in case you have multiple package id in your account, don’t specify the package id, remove it completely
//                 "oa" => $payload['alphanumeric'],
//                 "cpPassword" => md5(SDP_ID.SDP_PASSWORD2.SDP_TIMESTAMP),
//                 "msisdn" => implode(',',$payload['contacts']),
//                 "message" => $payload['message'],
//                 "uniqueId" => $payload['messageId'],
//                 "actionResponseURL" => SDP_CALLBACK."sdp/v1/dlrbulk"
//             ]
//         ]
//     ];
//     // log
//     writeToFile('/tmp/'.date('Y-m-d').'-techpitch-sdp-bulk.log', json_encode($request,JSON_PRETTY_PRINT));
//     return json_encode([
//         "keyword" => "Bulk",
//         "status" => "SUCCESS",
//         "statusCode" => "SC0000"
//     ]);
//     return callAPI("POST", SDP1.'api/public/CMS/bulksms', $headers, $request);    
// }

// POST request only
if(!ReqPost()) ReqBad();

$headers = getallheaders();
$customerId = $headers['Customerid'];
$pgroupId = $headers['Groupid'];
$alphanumeric = $headers['Alphanumeric'];
$alphanumericId = $headers['Alphanumericid'];

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);
$req['method'] = $headers['Method'];
$req['pgroupId'] = $pgroupId;
$req['customerId'] = $customerId;

// 2. Validate

if($req['scheduled']) $req['scheduled'] = date('Y-m-d H:i:s',strtotime($req['scheduled']));
// else unset($req['scheduled']);

// A. Save as draft
if($req['status']==1){
    // save into mysql
    $dbdata = [
        'action' => 6,
        'recepients' => 0,
        'title' => $req['title'],
        'message' => $req['message'],
        'alphanumeric' => $alphanumeric,
        'alphanumericId' => $alphanumericId,
        'transactionId' => 1,
        'customerId' => $customerId,
        'pgroupId' => $pgroupId,
        'groups' => $req['contactGroupId'],
        'method' => $req['method'],
        'scheduled' => $req['scheduled'],
        'contacts' => 0,
        'units' => 0,
        'statusId' => $req['status'],
    ];

    $return = PROC(Message($dbdata))[0][0];

    if(isset($return['created']) && $return['created']>0){
        // save into mongo
        
        $dbdata['_id'] = $return['messageId'];
        $dbdata['method'] = $req['method'];
        $dbdata['status'] = 'draft';
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
    exit;    
}




// 3. Check for balance

$headers = [
    'Groupid: '.$pgroupId,
    'Customerid: '.$customerId,
    'Alphanumeric: '.$alphanumeric,
    'Alphanumericid: '.$alphanumericId
];

if(isset($req['contactGroupId'])){
    $url = API_HOST.'group/v1/view';
    $contacts = json_decode(callAPI('GET',$url,$headers,['groupId' => $req['contactGroupId']]),1);
    $req['contacts'] = array_column($contacts[0]['contacts'],'phone');
}

// 4. Calculate the number of sms
$units = ceil(strlen($req['message'])/SMS_CHAR)*count($req['contacts']);

$request = ['units' => $units];
// print_r( $request ); exit;
// $balance = json_decode(callAPI('GET',$url,$headers),1);

// enhance it, to be a post, with units so to put aside units to be used by the queue, if it fails, then, reimburse
$url = API_HOST."payment/v1/balance"; 
$balance = json_decode(callAPI('POST',$url,$headers,$request),1); 

// echo $balance['balance'];

if($balance['balance'] >= $units && isset($balance['transactionId'])){
    $payload = [
        'method' => $req['method'],
        'title' => $req['title'],
        'message' => $req['message'],
        'contacts' => $req['contacts'],
        'alphanumeric' => $alphanumeric,
        'alphanumericId' => $alphanumericId,
        'scheduled' => $req['scheduled'],
        'transactionId' => $balance['transactionId'],
        'customerId' => $customerId,
        'pgroupId' => $pgroupId,
        'units' => $units,
        'statusId' => $req['status']
    ];

    $return1 = SaveMessage($payload); // print_r($return1); exit;

    if(isset($return1['created']) && $return1['created']>0){
        // Chunk the array into chunks
        $chunks = array_chunk($payload['contacts'],MESSAGE_CHUNKS);
        $messageId = validInt($return1['messageId']);

        // loop recepients saving them
        foreach ($chunks as $chunk) {
            $values = [];
            foreach ($chunk as $contact) {
                $values[] = "($messageId, '".validPhone($contact)."')";
            }
            $sql = "INSERT INTO messagesRecipients(messageId,phone) VALUES ".implode(',', $values);
            $return2 = query($sql);     
            // print_r($return2);
        }

        // preferable to push to SDP at this point

        // 3. send to sdp
        $payload['messageId'] = $messageId;

        // if scheduled skip sdp
        if($payload['statusId'] == 2){
            $response = [ 
                'status' => 200
            ];
            print_j($response);
            exit;     
        }

        $return4 = json_decode(bulkSDP($payload),1); // print_r($return4); exit;

        if(isset($return4['status']) && $return4['status'] == 'SUCCESS'){

            // 4. update mysql message to processing status
            $dbdata = [
                'action' => 4,
                'messageId' => $messageId,
                'pgroupId' => $pgroupId,
                'statusId' => 5
            ];
            $return = PROC(Message($dbdata)); // [0][0];
            // print_r($return); exit;

            // 4. update mysql payment
            $dbdata = [
                'action' => 9,
                'paymentId' => $payload['transactionId'],
                'customerId' => $messageId
            ];
            $return = PROC(Payment($dbdata)); // [0][0];
            // print_r($return);
            
            // update mongo
            // mongoUpdate(CMESSAGE,['_id'=>$messageId],['status'=>'processing','statusId'=>3]);

            // update mongo
            $filter = [ '_id' => $messageId ];
            $update = [ 
                'status' => 'processing',
                'statusId' => 5
            ];
            $return2 = mongoUpdate(CMESSAGE,$filter,$update); // print_r($return2); exit;

            $response = [
                'status' => 200,
                'message' => "successful"
            ];              
        }

    } else {
        // log failed to save
        $response = [
            'status' => 403,
            'message' => "Failed to save into mysql"
        ];        
    }


} else {
    // 
    $response = [
        'status' => 403,
        'replenish' => 1,
        'message' => "Insufficient funds, replenish via stkpush to continue"
    ];
}

print_j($response);