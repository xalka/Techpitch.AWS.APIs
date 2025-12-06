<?php

function deny(){
    header("HTTP/1.0 403 Forbidden");
    http_response_code(403);
    header('Content-Type: application/json');
    print_r(json_encode(['auth'=>0]));
    exit;
}

function allow(){
    http_response_code(200);
    header('Content-Type: application/json');
    print_r(json_encode(['auth'=>1]));
    exit;
}

if($_SERVER['REQUEST_METHOD']!=='POST') deny();

require __dir__.'/.config.php';
require __dir__.'/../../.proc/.db.php';

$headers = getallheaders();

// use token only

if(!isset($headers['token']) || !isset($headers['Username'])) deny();

$info = json_decode(redisGetValue($key),1);

// Introduce businessId
// if(isset($info['token']) && $info['token']==$headers['token'] && $headers['Businessid']==$info['businessId'] ) allow();
if(isset($info['token']) && $info['token']==$headers['token']) allow();
else deny();