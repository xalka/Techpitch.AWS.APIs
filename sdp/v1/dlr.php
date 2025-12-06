<?php

// Ensure whitelisting of sdp ips

// 1. Queue to kafka

// 2. Process from kafka

// 3. Update mysql
    // 1. Update the message
    // 2. Update each number if delivered, block and pending
    // 3. If blocked, update the group with numbers blocked per alphanumeric

// 4. Insert & Update mongodb

require_once __dir__.'/../../.config/.config.php'; 
require_once __dir__.'/../../.core/.funcs.php'; 

if(!ReqPost()) ReqBad();

require_once __dir__.'/../../.core/.mysql.php'; 
require_once __dir__.'/../../.core/.procedures.php';
require_once __dir__.'/../../.core/.mongodb.php';
require_once __dir__.'/../../.core/.redis.php';

$token = json_decode(redisGetValue(SDP_TOKEN),1);

$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-Authorization: Bearer '.$token
];


function bulkDeliveryReceipt($data){
    $dbdata = [
        'action' => 5,
        'statusId' => 8,
        'sentAt' => $data['requestTimeStamp'],
    ];
    foreach ($data['requestParam']['data'] as $value) { 
        if($value['name'] === 'Msisdn') $dbdata['phone'] = validPhone($value['value']);
        if($value['name'] === 'correlatorId') $dbdata['messageId'] = $value['value'];
    }

    // print_r($dbdata);
    $dlr = PROC(Message($dbdata))[0][0];

    $dlr['_id'] = validInt($dlr['recipientId']);
    $dlr['messageId'] = validInt($dlr['messageId']);
    $dlr['blocked'] = validInt($dlr['blocked']);
    $dlr['groupId'] = validInt($dlr['groupId']);
    $dlr['created'] = mongodate($dlr['created']);
    $dlr['delivered'] = mongodate($dlr['delivered']);

    // unset($dlr['recipientId']);
    // unset($dlr['sentAt']);
    // unset($dlr['deliveredAt']);
    // unset($dlr['updated']);
    
    $dlr = array_diff_key($dlr, array_flip(['recipientId', 'updated']));

    print_r(mongoInsert(CDLR,$dlr));
}

function MTPayload($requestId,$LinkId, $OfferCode, $Msisdn){
    return [
        "requestId" => $requestId, // could it be the message id to be used in dlr
        "channel" =>  "SMS",
        "operation" =>  "SendSMS",
        "requestParam"  =>  [
            "data"  =>  [
                ["name" =>  "LinkId", "value" =>  $LinkId],
                ["name" =>  "Msisdn", "value" =>  $Msisdn],
                ["name" =>  "Content", "value" =>  "Thank You for Ondemand Subscription SAFRI TEST TUN Subscption test Send sms"],
                ["name" =>  "OfferCode", "value" =>  $OfferCode], 
                ["name" =>  "CpId", "value" => SDP_ID],
            ]
        ]
    ];
}

function MT($data){
    global $headers;
    
    foreach ($data['requestParam']['data'] as $item) {
        if($item["name"] === "LinkId"){
            $LinkId = $item["value"];
        }
        if($item["name"] === "OfferCode"){
            $OfferCode = $item["value"];
        }
        if($item["name"] === "Msisdn"){
            $Msisdn = $item["value"];
        }
    }

    // process the data
    // check for the subscriber
    // check for the offer
    // send sms

    $request = MTPayload($data['requestId'], $LinkId, $OfferCode, $Msisdn);

    $return = callAPI("POST", SDP1.'api/public/SDP/sendSMSRequest', $headers, $request);
    
    print_r($return);    
}

function MO($data){
    // expected payload
    /*
            {
            "requestId": "46735",
            "requestTimeStamp": "2020-01-17 17:17:17",
            "operation": "INTERACTIVE",
            "requestParam": {
                "data": [
                {
                    "name": "Msisdn",
                    "value": "254795421629"
                },
                {
                    "name": "Command",
                    "value": "CP_NOTIFICATION"
                }
                ],
                "additional data": [
                {
                    "name": "DA",
                    "value": "9004"
                },
                {
                    "name": "SMS",
                    "value": "HGGJJ"
                }
                ]
            }
            }    
    */
    echo  "The service to be undertaken";
}

function Subscription($data){
    print_r($data);
}

function OfferSubscription($data){
    print_r($data);
}

$results = file_get_contents('php://input');

writeToFile(LOG_SDP,$results);

$results = json_decode($results,1); 

if(isset($results['operation'])):

    switch (strtolower($results['operation'])) {

        // bulk sms
        case 'deliveryreceipt':
            bulkDeliveryReceipt($results);
            break;

        // receiving MT sms
        case 'cp_notification':
            if(isset($results['requestParam']['data'])){
                foreach ($results['requestParam']['data'] as $item) {
                    if ($item["name"] === "Type"){
                        switch ($item["value"]) {

                            case 'NOTIFY_LINKID':
                                MT($results);
                                break;
                            
                            case 'ACTIVATION':
                                OfferSubscription($results);
                                break;
                            
                            default:
                                # code...
                                break;
                        }
                    }
                }
            };
            break;

        // receiving MO sms
        case 'interactive':
            MO($results);
            break;

        // subscription inititate by the cp
        case 'activate':
            Subscription($results);
            break;            

        default:
            $results['operation'] = 'Unknown';
            break;
    }

endif;