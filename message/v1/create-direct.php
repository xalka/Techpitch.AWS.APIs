<?php

require_once __dir__.'/../../.config/.config.php'; 
require_once __dir__.'/../../.core/.funcs.php'; 

// POST request only
if(!ReqPost()) ReqBad();

require_once __dir__.'/../../.core/.mysql.php'; 
require_once __dir__.'/../../.core/.mongodb.php';
require_once __dir__.'/../../.core/.redis.php'; 
require_once __dir__.'/../../.core/.procedures.php';
// require_once __dir__.'/../../.core/Kafka/KafkaClient.php';

$headers = getallheaders();

// Receive json
$req = json_decode(file_get_contents('php://input'),1);

writeToFile(LOG_FILE,json_encode($req,JSON_UNESCAPED_UNICODE));

$customerId = $headers['Customerid'];
$pgroupId = $headers['Groupid'];

$headers = [
    'Content-Type: application/json',
    'Customerid: '. $customerId,
    'Groupid: '. $pgroupId
];

// get alphanumeric
if(isset($req['alphanumericId']) && $req['alphanumericId'] ){
    
    $url = API_HOST."alphanumeric/v1/view";
    $request = [ 'alphanumericId' => validInt($req['alphanumericId']) ];
    $alphanumeric = json_decode(callAPI('GET',$url,$headers,$request),1);

    $req['alphanumericId'] = $alphanumeric[0]['_id'];
    $req['alphanumeric'] = $alphanumeric[0]['title'];
            
} else {
    $req['alphanumericId'] = SDP_ALPHANUMERIC_ID;
    $req['alphanumeric'] = SDP_ALPHANUMERIC;    
}

$req['typeId'] = isset($headers['Method']) && $headers['Method']=='transaction' ? 1 : 2;
$req['pgroupId'] = $pgroupId;
$req['customerId'] = $customerId;


// Validate


if(isset($req['contactGroupId']) && $req['contactGroupId']){
    $url = API_HOST.'group/v1/view';
    $request = ['id' => $req['contactGroupId']];
    $contacts = json_decode(callAPI('GET',$url,$headers,$request),1);
    // $req['contacts'] = $contacts[0]['contacts'];
    $req['recipients'] = $contacts[0]['count'];

} else {
    $req['recipients'] = count(array_column($req['contacts'],'phone'));
}

// Calculate the number of sms
$units = ceil(strlen($req['message'])/SMS_CHAR)*$req['recipients'];

// enhance it, to be a post, with units so to put aside units to be used by the queued, schedules, if it fails, then, reimburse
$url = API_HOST."payment/v1/balance";
$request = ['units' => $units];
$balance = json_decode(callAPI('POST',$url,$headers,$request),1); 

if($balance['balance'] >= $units && isset($balance['transactionId'])){
    $payload = [
        'headers' => $headers,
        'title' => $req['title'],
        'message' => $req['message'],
        'contactGroupId' => $req['contactGroupId'] ?? null,
        'contacts' => $req['contacts'] ?? [],
        'recipients' => $req['recipients'],
        'alphanumeric' => $req['alphanumeric'],
        'alphanumericId' => $req['alphanumericId'],
        'scheduled' => $req['scheduled'] ?? null,
        'transactionId' => $balance['transactionId'],
        'customerId' => $customerId,
        'pgroupId' => $pgroupId,
        'units' => $units,
        'statusId' => 4,
        'status' => 'queued',
        'typeId' => $req['typeId'],
        'mode' => $req['mode']
    ];
    
    writeToFile(LOG_FILE,$request);

    // $payload = json_decode($message->payload,1);

    // $headers = $payload['headers'];

    // check if it has contacts
    if(!isset($payload['contacts']) || empty($payload['contacts']) || count($payload['contacts'])==0){
        
        $url = API_HOST."group/v1/detail";
        $loop = true;
        $start = 0;
        $limit = 20;

        while($loop){
            $request = [
                'id' => $payload['contactGroupId'],
                'start' => $start,
                'limit' => $limit
            ];
            $contacts = json_decode(callAPI('GET',$url,$headers,$request),1);
            
            if(count(array_column($contacts[0]['contacts'],'phone'))==$limit) $start += 1;
            else $loop = false;
            
            $payload['contacts'] = array_merge($payload['contacts'],$contacts[0]['contacts']);
        }
    }
    
    if($payload['statusId'] == 4){
        // save into DB
        $return = SaveMessage($payload);
        if(isset($return['_id']) && $return['_id']>0){
            $payload['messageId'] = validInt($return['_id']);
            $response = saveMessageRecipients($payload);
            if($response['status']==500){
                echo "Technical problem";
            }
        }
    }

    // send to sdp
    // check if its normal or custom
    if($payload['mode']==1){
        $chunks = array_chunk($payload['contacts'],MESSAGE_CHUNKS);
        
        foreach ($chunks as $chunk) {
            foreach ($chunk as $contact) { 
                // advance: {variables} to be parameter or read from excel column
                $message = $payload;
                $message['contacts'] = [$contact];
                $message['message'] = str_replace("{name}", $contact['fname'], $message['message']);
                
                $return = json_decode(bulkSDP($message),1);
                print_r(json_encode($return));
            }    
            sleep(1);
        }

    } else {
        $return = json_decode(bulkSDP($payload),1);
        print_r(json_encode($return));
    }

    if(isset($return['status']) && $return['status'] == 'SUCCESS'){
        $messageId = validInt($payload['messageId']);

        // 4. update mysql message to processing status
        $dbdata = [
            'action' => 4,
            'messageId' => $messageId,
            'pgroupId' => $payload['pgroupId'],
            'statusId' => 5
        ];
        $return = PROC(Message($dbdata));

        // 4. update mysql payment
        $dbdata = [
            'action' => 9,
            'paymentId' => $payload['transactionId'],
            'messageId' => $messageId,
            'groupId' => $payload['pgroupId']
        ];
        $return = PROC(Payment($dbdata)); // [0][0];

        // update mongo
        $filter = [ '_id' => $messageId ];
        $update = [ 
            'status' => 'processing',
            'statusId' => 5
        ];
        $return2 = mongoUpdate(CMESSAGE,$filter,$update); // print_r($return2); exit;

        echo "Done: $messageId \n\n";
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