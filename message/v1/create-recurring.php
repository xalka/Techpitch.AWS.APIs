<?php

// prevent direct access
if (!defined('DIRECT_ACCESS')) {
    header('HTTP/1.0 400 Bad Request');
    http_response_code(400);
    exit;
}

if(isset($req['contactGroupId'])){
    $url = API_HOST.'group/v1/view';
    $request = ['id' => $req['contactGroupId']];
    $contacts = json_decode(callAPI('GET',$url,$headers,$request),1); // print_r($contacts); exit;
    // $req['contacts'] = $contacts[0]['contacts'];
    $req['recipients'] = $contacts[0]['count'];

} else {
    $req['recipients'] = count(array_column($req['contacts'],'phone'));
}

// Calculate the number of sms
$units = ceil(strlen($req['message'])/SMS_CHAR)*$req['recipients'];

// enhance it, to be a post, with units so to put aside units to be used by the queued, schedules, if it fails, then, reimburse
// $url = API_HOST."payment/v1/balance";
// $request = ['units' => $units];
// $balance = json_decode(callAPI('POST',$url,$headers,$request),1); 

// if($balance['balance'] >= $units && isset($balance['transactionId'])){
    $request = [
        'headers' => $headers,
        'title' => $req['title'],
        'message' => $req['message'],
        'contactGroupId' => $req['contactGroupId'] ?? null,
        'contacts' => $req['contacts'] ?? [],
        'recipients' => $req['recipients'],
        'alphanumeric' => $req['alphanumeric'],
        'alphanumericId' => $req['alphanumericId'],
        // 'scheduled' => $req['scheduled'] ?? null,
        // 'transactionId' => $balance['transactionId'],
        'customerId' => $customerId,
        'pgroupId' => $pgroupId,
        'units' => $units,
        'statusId' => 3,
        'status' => 'recurring',
        'typeId' => $req['typeId'],
        'mode' => $req['mode']
    ];
    
    switch ($req['recurrence']) {
        case '1':
        case '3':
        case '4':
            $request['recurrence'] = [
                'recurrence' => $req['recurrence'],
                'date' => $req['date']
            ];
            break;

        case '2':
            $request['recurrence'] = [
                'recurrence' => $req['recurrence'],
                'weekly' => $req['weekly'],
                'time' => $req['time']
            ];
            break;
        
        default:
            # code...
            break;
    }

    // print_j($request); exit;
    
    writeToFile(LOG_FILE,$request);

    try {
        $kafkaClient = new KafkaClient(KAFKA_BROKER);
        $kafkaClient->produceMessage(KAFKA_RECURRING_SMS_TOPIC, json_encode($request),$partition=RD_KAFKA_PARTITION_UA);

        $response = [
            'status' => 200
        ]; 
        
        // Produce multiple messages
        /*$messages = [];
        for ($i = 0; $i < 10; $i++) {
            $messages[] = "Message " . $i . ' ' . time();
        }
        $kafkaClient->produceMessages($topicName, $messages);
        */
        
        // Flush pending messages
        $kafkaClient->flush();        

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        $response = [
            'status' => 400,
            'error' => $e->getMessage()
        ];        
    }    


// } else {
//     // 
//     $response = [
//         'status' => 403,
//         'replenish' => 1,
//         'message' => "Insufficient funds, replenish via stkpush to continue"
//     ];
// }

print_j($response);