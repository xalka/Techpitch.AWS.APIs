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

$return = json_decode(callAPI('POST', $url, $headers, $request),1);

if(isset($return['token'])){
    // redisSetValue(SDP_REFRESH_TOKEN,json_encode($return['refreshToken']),SDP_TOKEN_DURATION);
    redisSetValue(SDP_REFRESH_TOKEN,json_encode($return['refreshToken']));
    redisSetValue(SDP_TOKEN,json_encode($return['token']));
    echo "Successful\n";
}

// send email | SMS to admins
// log