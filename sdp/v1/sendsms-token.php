<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.redis.php';

// $token = json_decode(redisGetValue(SDP_TOKEN),1);
// $tokenII = json_decode(redisGetValue(SDP_REFRESH_TOKEN),1);

// 1. --------------- Restore token start  ----------------------
$url = SDP1."api/auth/login";
$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
];
$request = [
    "username" => SDP_USERNAME1,
    "password" => SDP_PASSWORD1
];
$refreshToken = json_decode(callAPI('POST', $url, $headers, $request),1); 
// print_r($refreshToken['token']); exit;
//  --------------- Restore token end  ----------------------


// 2. ------------------ Generate token start  ----------------------
/*$url = SDP1."api/auth/RefreshToken";
$headers = [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-Authorization: Bearer '.$refreshToken['refreshToken']
];
// print_r($headers);
$token = json_decode(callAPI('GET', $url, $headers),1); // print_r($token); exit;
*/
// ------------------ Generate token end  ----------------------

$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    // 'X-Authorization: Bearer '.$token['token'] 
    'X-Authorization: Bearer '.$refreshToken['token']
];
// print_j($headers); exit;
// $request = [
//     "requestId" =>  time(),
//     "channel" =>  "SMS",
//     "requestParam"  =>  [
//         "operation" =>  "SendSMS",
//         "data"  =>  [
//             ["name" =>  "LinkId", "value" =>  "00010310189519161781865526"],
//             ["name" =>  "Msisdn", "value" =>  "254715003414"],
//             ["name" =>  "Content", "value" =>  "Thank You for Ondemand Subscription SAFRI TEST TUN Subscption test Send sms"],
//             ["name" =>  "OfferCode", "value" =>  "001075102371"],
//             ["name" =>  "CpId", "value" => "751"],
//         ]
//     ]
// ];
// print_j($request); exit;

$request = [
    "requestId" =>  time(),
    "channel" =>  "SMS",
    "operation" =>  "SendSMS",
    "requestParam"  =>  [
        "data"  =>  [
            ["name" =>  "LinkId", "value" =>  "00751110184965024122126567"],
            ["name" =>  "Msisdn", "value" =>  "254701380001"],
            ["name" =>  "Content", "value" =>  "Thank You for Ondemand Subscription SAFRI TEST TUN Subscption test Send sms"],
            ["name" =>  "OfferCode", "value" =>  "001075102371"], 
            ["name" =>  "CpId", "value" => "751"],
        ]
    ]
];

$return = callAPI("POST", SDP1.'api/public/SDP/sendSMSRequest', $headers, $request);

print_r($return);
// save to redis

// {
//     "keyword": "Bulk",
//     "status": "SUCCESS",
//     "statusCode": "SC0000"
// }