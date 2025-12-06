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

$refreshToken = json_decode(redisGetValue(SDP_REFRESH_TOKEN),1);// print_r($refreshToken);

$url = SDP1."api/auth/RefreshToken";
$headers = [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-Authorization: Bearer '.$refreshToken
];
// print_r($headers);
$return = json_decode(callAPI('GET', $url, $headers),1); // print_r($return);

if(isset($return['token'])){
    unset($return['msg']);
    // redisSetValue(SDP_TOKEN,json_encode($return['token']),SDP_TOKEN_DURATION);
    redisSetValue(SDP_TOKEN,json_encode($return['token']));
    echo "Successful\n";
}

// send email | SMS to admins
// log