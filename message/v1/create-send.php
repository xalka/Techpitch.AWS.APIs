<?php

// prevent direct access
if (!defined('DIRECT_ACCESS')) {
    header('HTTP/1.0 400 Bad Request');
    http_response_code(400);
    exit;
}

if(isset($req['contactGroupId'])){
    $url = API_HOST.'group/v1/view';
    $request = [
        'groupId' => validInt($req['contactGroupId'])
    ]; 
    $contacts = json_decode(callAPI('GET',$url,$headers,$request),1);
    $req['recipients'] = $contacts[0]['contacts'];

    if(empty($req['recipients'])){
        $response = [
            'status' => 403,
            'message' => "The group doesn't have any contacts"
        ];
        exit(print_j($response));
    }

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

    try {

        $request = [
            'action' => 1,
            'title' => $req['title'],
            'message' => $req['message'],
            'contactGroupId' => $req['contactGroupId'] ?? null,
            // 'contacts' => $req['contacts'] ?? [],
            'recipients' => $req['recipients'],
            'alphanumeric' => $req['alphanumeric'],
            'alphanumericId' => $req['alphanumericId'],
            // 'scheduled' => $req['scheduled'] ?? null,
            'transactionId' => $balance['transactionId'],
            'customerId' => $customerId,
            'pgroupId' => $pgroupId,
            'units' => $units,
            'statusId' => 4,
            'status' => 'queued',
            'typeId' => $req['typeId'],
            'mode' => $req['mode'],
            'sent' => 0
        ];
        
        $return = PROC(Message($request)); // [0][0];    
        
        if(isset($return[0][0]['messageId']) && $return[0][0]['messageId'] > 0){
            $messageId = validInt($return[0][0]['messageId']);
            $request['messageId'] = $messageId;
        } else {
            $response = [
                'status' => 400,
                'error' => 'Failed to save message'
            ];
            print_j($response);
            exit;
        }

        // save into mongodb
        $request['_id'] = $messageId;
        unset($request['action']);

        $return2 = mongoInsert(CMESSAGE,$request); 

        if($return2){
            $response = [ 
                '_id' => $dbdata['_id'],
                'created' => 1
            ];
        } else {
            $response = [
                'created' => 0,
                'message' => 'Unable to save into mongodb'
            ];
        }  

        $request['headers'] = $headers;
       
        $kafka = new KafkaHelper(KAFKA_BROKER);

        if(isset($req['contacts']) && !empty($req['contacts'])){
            unset($request['contactGroupId']);
            foreach (array_chunk($req['contacts'],GROUP_CHUNKS) as $batch) { 
                $request['contacts'] = $batch;
                $kafka->produceMessage(KAFKA_SEND_BULK_TOPIC,json_encode($request));
            }

        } else {
            $kafka->produceMessage(KAFKA_SEND_BULK_TOPIC,json_encode($request));
        }
        $kafka->flush();

        $response = [
            'status' => 200
        ]; 


    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        $response = [
            'status' => 400,
            'error' => $e->getMessage()
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