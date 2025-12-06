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

$return = json_decode(callAPI('POST', $url, $headers, $request),1); // print_r($return['refreshToken']);

if(isset($return['token'])){
    // redisSetValue(SDP_REFRESH_TOKEN,json_encode($return['refreshToken']),SDP_TOKEN_DURATION);
    redisSetValue(SDP_REFRESH_TOKEN,json_encode($return['refreshToken']));
    echo "Successful\n";
}

// send email | SMS to admins
// log