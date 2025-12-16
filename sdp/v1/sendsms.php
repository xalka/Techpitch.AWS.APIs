<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

echo "\nStart ..... \n";

$baseDir = dirname(__dir__,2);
$files = [
    '/.config/.config.php',
    '/.core/.funcs.php',
    '/.core/RedisHelper.php',
    '/.core/SDP.php'
];
foreach ($files as $file) require_once $baseDir.$file;

$redis = new RedisHelper();
$token = $redis->get(SDP_TOKEN);

$payload = [
    'token' => $token,
    'username' => SDP_USERNAME2,
    "password" => SDP_PASSWORD2,
    'shortcode' => SDP_ALPHANUMERIC,
    'timestamp' => SDP_TIMESTAMP,
    'contacts' => "254715003414,254722636396",
    'message' => "Testing bulk sms",
    'messageId' => 'Tp'.(String)time(),
];

$sdp = new SDP();
// $response = json_decode($sdp->sendSMS($payload),1);
$response = $sdp->sendSMS($payload);
print_r($response);


echo "\nDone.";

// {
//     "keyword": "Bulk",
//     "status": "SUCCESS",
//     "statusCode": "SC0000"
// }