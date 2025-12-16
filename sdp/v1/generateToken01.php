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

$refreshToken = json_decode(redisGetValue(SDP_REFRESH_TOKEN),1);// print_r($refreshToken);

$url = SDP1."api/auth/RefreshToken";
$headers = [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-Authorization: Bearer '.$refreshToken
];

$return = json_decode(callAPI('GET', $url, $headers),1); // print_r($return);

if(isset($return['token'])){
    unset($return['msg']);
    // redisSetValue(SDP_TOKEN,json_encode($return['token']),SDP_TOKEN_DURATION);
    redisSetValue(SDP_TOKEN,json_encode($return['token']));
    echo "Successful\n";
}

// send email | SMS to admins
// log