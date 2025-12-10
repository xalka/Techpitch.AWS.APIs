<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

$baseDir = dirname(__dir__,2);
$files = [
    '/.config/.config.php',
    '/.core/.funcs.php',
    '/.core/.redis.php'
];
foreach ($files as $file) require_once $baseDir.$file;

$token = json_decode(redisGetValue(SDP_TOKEN),1); // print_r($tokens); exit;
// $token = (String)redisGetValue(SDP_TOKEN); //print_r($token); exit;

$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-Authorization: Bearer '.$token
];

$request = [
    "timeStamp" => SDP_TIMESTAMP,
    "dataSet" => [
        [
            "userName" => SDP_USERNAME2, // SDP_USERNAME1
            "channel" => "sms",
            # "packageId" => 4391, // in case you have multiple package id in your account, don’t specify the package id, remove it completely
            "oa" => "TestTP", // TestSender
            "cpPassword" => md5(SDP_ID.SDP_PASSWORD2.SDP_TIMESTAMP), //  SDP_PASSWORD1
            "msisdn" => "254715003414,254722636396",
            "message" => "Testing bulk sms",
            "uniqueId" => (String)time(),
            // "actionResponseURL" => "ed5fb638de86b8d94a85116e1e1c8d6af8655b1847c4e433e9ca0bae414be15.techxal.co.ke/bulkdlr"
            "actionResponseURL" => SDP_CALLBACK.'sdp/v1/dlrbulk'
        ]
    ]
];

$return = callAPI("POST", SDP1.'api/public/CMS/bulksms', $headers, $request);

print_r($return); exit;
// save to redis

// {
//     "keyword": "Bulk",
//     "status": "SUCCESS",
//     "statusCode": "SC0000"
// }


echo "\n\nEnd.";