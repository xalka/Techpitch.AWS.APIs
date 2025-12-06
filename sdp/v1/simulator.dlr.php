<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mongodb.php';

function dlrObj($message){
    return [
        "requestId" => "1740487593180",
        "requestTimeStamp" => "2025-02-25 15:46:33",
        "channel" => "SMS",
        "operation" => "DeliveryReceipt",
        "traceID" => "7837$195233235573068077",
        "requestParam" => [
            "data" => [
                [
                    "name" => "Msisdn",
                    "value" => $message['phone']
                ],
                [
                    "name" => "CpId",
                    "value" => "102126"
                ],
                [
                    "name" => "correlatorId",
                    "value" => $message['messageId']
                ],
                [
                    "name" => "Description",
                    "value" => "DeliveredToTerminal"
                ],
                [
                    "name" => "deliveryStatus",
                    "value" => "0"
                ],
                [
                    "name" => "Type",
                    "value" => "DELIVER_RECEIPT(Bulk)"
                ],
                [
                    "name" => "campaignId",
                    "value" => "7837"
                ]
            ]
        ]
    ];    
}

// use mongodb instead of mysql

// 1. Get schedule sms
$sql = "SELECT mr.phone, m.messageId
        FROM messagesRecipients mr
        JOIN messages m ON m.messageId = mr.messageId
        WHERE 
	        m.statusId = 5
            AND m.created <= NOW() - INTERVAL 10 MINUTE
        limit 100";         
	        
try {
    $messages = query($sql);

    if(empty($messages)){
        print_j(['status' => 200,'message' => 'No scheduled messages']);
        exit;
    }   

    // 2. Queue into kafka for processing
    $messageId = null;
    foreach ($messages as $message) { 
        $messageId = $message['messageId'];
        $headers = [];
        $request = dlrObj($message);
        $url = API_HOST.'sdp/v1/dlr';

        $report = json_decode(callAPI('POST',$url,$headers,$request),1);  
        
        $response = [
            'status' => 200
        ];
        
        print_r($report);
        sleep(1);
    }

    query("UPDATE messages SET statusId = 8 WHERE messageId = $messageId");

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $response = [
        'status' => 400,
        'error' => $e->getMessage()
    ];        
}

print_r($response);