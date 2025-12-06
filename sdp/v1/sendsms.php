<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.redis.php';

$token = json_decode(redisGetValue(SDP_TOKEN),1);
// $tokenII = json_decode(redisGetValue(SDP_REFRESH_TOKEN),1);

$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-Authorization: Bearer '.$token
];
// print_j($headers); exit;
$request = [
    "requestId" =>  time(),
    "channel"   =>  "SMS",
    "operation" =>  "SendSMS",
    "requestParam"  =>  [
        "data"  =>  [
            ["name" =>  "LinkId", "Value" =>  "00010310189519161781865526"],
            ["name" =>  "Msisdn", "Value" =>  254715003414],
            ["name" =>  "Content", "Value" =>  "Thank You for Ondemand Subscription SAFRI TEST TUN Subscption test Send sms"],
            ["name" =>  "OfferCode", "Value" =>  "001075102371"],
            ["name" =>  "CpId", "Value" =>  SDP_ID],
        ]
    ]
];
// print_j($request); exit;
$return = callAPI("POST", SDP1.'api/public/SDP/sendSMSRequest', $headers, $request);

print_r($return);
// save to redis

// {
//     "keyword": "Bulk",
//     "status": "SUCCESS",
//     "statusCode": "SC0000"
// }