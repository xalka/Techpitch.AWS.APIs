<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

$baseDir = dirname(__dir__,2);
$files = [
    '/.config/.config.php',
    '/.core/.funcs.php',
    '/.core/RedisHelper.php',
    '/.core/SDP.php'
];
foreach ($files as $file) require_once $baseDir.$file;

$sdp = new SDP();
$payload = [
    "username" => SDP_USERNAME1,
    "password" => SDP_PASSWORD1
];
$response = json_decode($sdp->generateFreshingToken($payload),1);

if(isset($response['token'])){
    $redis = new RedisHelper();
    $value = $redis->set(SDP_REFRESH_TOKEN,$response['refreshToken'],SDP_TOKEN_DURATION);
    $value = $redis->set(SDP_TOKEN,$response['token'],SDP_TOKEN_DURATION);
    echo "Successful\n";
}