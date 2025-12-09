<?php
echo "Starting ...";
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

$tokens = json_decode(redisGetValue(SDP_TOKENS),1);

$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-Authorization: Bearer '.$tokens['token']
];

$request = [
    "timeStamp" => TIMESTAMP,
    "dataSet" => [
        [
            "userName" => SDP_USERNAME,
            "channel" => "sms",
            # "packageId" => 4391, // in case you have multiple package id in your account, don’t specify the package id, remove it completely
            "oa" => "TestSender",
            "cpPassword" => md5(SDP_ID.SDP_PASSWORD.TIMESTAMP), //  "8912e466bad6bcd3784e07946e2d04ce", // cpPassword = MD5(cpId + Password + timestamp)
            "msisdn" => "254715003414,254722636396",
            "message" => "Great deals await you! Get 20% off on all products this weekend only. Shop now at www.example.com. Offer valid till Sunday!",
            "uniqueId" => (String)time(),
            "actionResponseURL" => "ed5fb638de86b8d94a85116e1e1c8d6af8655b1847c4e433e9ca0bae414be15.techxal.co.ke/bulkdlr"
        ]
    ]
];

$return = callAPI("POST", SDP1.'api/public/CMS/bulksms', $headers, $request);

print_j($return); exit;
// save to redis

// {
//     "keyword": "Bulk",
//     "status": "SUCCESS",
//     "statusCode": "SC0000"
// }


echo "\n\nEnd.";