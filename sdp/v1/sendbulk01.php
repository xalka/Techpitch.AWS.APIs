<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
// require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.redis.php'; 
// require __dir__.'/../../.core/Redis/Connection.php'; 
// require __dir__.'/../../.core/Redis/Queue.php'; 
// require __dir__.'/../../.core/.mongodb.php';
// require __dir__.'/../../.core/.procedures.php';

$token = json_decode(redisGetValue(SDP_TOKEN),1);

$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-Authorization: Bearer '.$token
];
print_r(json_encode($headers,JSON_PRETTY_PRINT));
$request = [
    "timeStamp" => SDP_TIMESTAMP,
    "dataSet" => [
        [
            "userName" => SDP_USERNAME2,
            "channel" => "SMS",
            //"packageId" => 10203, // in case you have multiple package id in your account, don’t specify the package id, remove it completely
            "oa" => "TestTP",
            "cpPassword" => md5(SDP_ID.SDP_PASSWORD2.SDP_TIMESTAMP), //  "8912e466bad6bcd3784e07946e2d04ce", // cpPassword = MD5(cpId + Password + timestamp)
            "msisdn" => "254715003414,254722636396", // ,254115242477,254728642504,254722636396,254710543307",
            # "message" => "Great deals await you! Get 20% off on all products this weekend only. Shop now at www.example.com. Offer valid till Sunday!",
            "message" => "Testing bulk sms",
            "uniqueId" => 'Tp'.(String)time(),
            "actionResponseURL" => SDP_CALLBACK.'sdp/v1/dlrbulk'
        ]
    ]
];
print_r(json_encode($request,JSON_PRETTY_PRINT));
$return = callAPI("POST", SDP1.'api/public/CMS/bulksms', $headers, $request);
echo SDP1.'api/public/CMS/bulksms';
print_r($return);
// save to redis

// {
//     "keyword": "Bulk",
//     "status": "SUCCESS",
//     "statusCode": "SC0000"
// }