<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
// require __dir__.'/../../.config/.mysql.php'; 
// require __dir__.'/../../.config/.mongodb.php';
// require __dir__.'/../../.config/.procedures.php';

// POST request only
if(!ReqPost()) ReqBad();

$req = json_decode(file_get_contents('php://input'),1);

$headers = getallheaders();

writeToFile(LOG_FILE,json_encode($req));

$errors = [];
$dbdata = [];

// {
//     "phone" : 254715003414,
//     "amount" : 1,
//     "reference" : 1,
//     "transactionId" : 1,
//     "descrp" : "Test"
// }

$required = ['amount','phone']; // referenceI, description, allocation
foreach ($required as $value) {
	if(!isset($req[$value]) || empty($req[$value])){
		$errors[$value] = "$value is required";
	}
}

if(validInt($req['amount'])){
	$dbdata['amount'] = validInt($req['amount']);
} else {
	$errors['amount'] = 'A valid amount is required';
}

if(validPhone($req['phone'])){
	$dbdata['phone'] = validPhone($req['phone']);
} else {
	$errors['phone'] = 'A valid phone number is required';
}

// $dbdata['saccoId'] = $headers['Saccoid'];
// $dbdata['adminId'] = $headers['Userid'];
// $dbdata['userId'] = validInt($req['userId']);

if(!empty($errors)){
	$errors['status'] = 401;
	$errors['message'] = "Invalid input";
	print_j($errors);
	exit;
}

// move to cronjob, and read from file
$url = MPESA_API.'oauth/v1/generate?grant_type=client_credentials';
$headers = ['Authorization: Basic '.base64_encode(MPESA_KEY.':'.MPESA_SECRET)];
$token = json_decode(callAPI('GET',$url,$headers),1);

// save the stk push data
$url = MPESA_API.'mpesa/stkpush/v1/processrequest';
$headers = ['Content-Type:application/json','Authorization:Bearer '.$token['access_token']];
$request = [
    'BusinessShortCode'     => MPESA_SHORTCODE,
    'Password'              => base64_encode(MPESA_SHORTCODE.MPESA_PASSKEY.TIMESTAMP),
    'Timestamp'             => TIMESTAMP,
    'TransactionType'       => 'CustomerPayBillOnline',
    'Amount'                => $dbdata['amount'],
    'PartyA'                => $dbdata['phone'],
    'PartyB'                => MPESA_SHORTCODE,
    'PhoneNumber'           => $dbdata['phone'],
    'CallBackURL'           => MPESA_CALLBACK.'v1/confirmer',
    'AccountReference'      => $req['reference'],
    'ThirdPartyTransID'     => $req['transactionId'],
    'TransactionDesc'       => $req['descrp']
];

$response = json_decode(callAPI('POST',$url,$headers,$request),1);

writeToFile(LOG_MPESA,json_encode($response,JSON_UNESCAPED_UNICODE));

if(isset($response['ResponseCode']) && $response['ResponseCode'] == 0){
	$response['code'] = 201;

} elseif(isset($response['errorCode'])){
    $response = [
		'code' => 400,
		'message' => $response['errorMessage']
	];	

} else {
    $response = [
		'code' => 400,
		'message' => "There is a technical problem"
	];
}

print_j($response);