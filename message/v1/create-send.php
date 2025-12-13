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
    // print_r($request); exit;
        // save into db    
        $return = PROC(Message($request)); // [0][0];    
        // print_r($return); exit;
        // save into mongodb
        
        // save into DB
        $return = SaveMessage($request);
        if(isset($return['_id']) && $return['_id']>0){
            $request['messageId'] = validInt($return['_id']);
        } else {
            $response = [
                'status' => 400,
                'error' => 'Failed to save message'
            ];
            print_j($response);
            exit;
        }
        
        unset($request['action']);

        $request['headers'] = $headers;
       
        $kafka = new KafkaHelper(KAFKA_BROKER);

        if(isset($req['contacts']) && !empty($req['contacts'])){
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