<?php

print_r('Generate');
exit;

require __dir__.'/.config.php';

if(!ReqJson() || !ReqPost()) ReqBad();

// authenticate user
require __dir__.'/../../.proc/.db.php';
require __dir__.'/../../.proc/.proc.php';
require __dir__.'/../../.proc/.mongodb.php';

$request = json_decode(file_get_contents('php://input'),1);
$headers = getallheaders();

$credentials = explode(':',base64_decode($headers['Authorization']));

// update mysql
$reference = validInt($request['reference']);
$key = $credentials[0];
$pass = $credentials[1];

$filter = [
	'reference' => $reference,
	'secretkey' => $key,
	'secretpass' => $pass
];
$options = [ 'projection' => ['businessId'=>1] ];

$return = mongoSelect($filter,$options);

// print_r($return); exit;

// $dbdata = [
// 	'action' => 3,
// 	'reference' => $reference,
// 	'key' => $key,
// 	'secret' => $pass,
// 	'token' => $token
// ];

// $return = proc(Business($dbdata))[0]; 

// update redis
if(!empty($return) && isset($return[0]) && $return[0]->_id){
	$token = generateToken($key);
	$expiry = date('Y-m-d H:i:s',time()+TOKEN_EXPIRY);

	$obj = [
		'token' => $token,
		'expiry' => $expiry,
		'businessId' => $return[0]->businessId,
	];

	redisSetValue($reference,enJson($obj),TOKEN_EXPIRY);

	$obj['reference'] = $reference;
	$obj['key'] = $key;
	$obj['secret'] = $pass;

	redisPub(REDIS_TOKEN_CHANNEL,enJson($obj));

	// response with the token
	enJsonPrint(array(
			'token' => $obj['token'],
			'expiry' => $obj['expiry']
		)
	);	
} else ReqBad();