<?php

// $url = "https://api.techxal.co.ke/oauth/v1/generate";
$url = "http://dev-api.techxal.co.ke/oauth/v1/generate";

$username = 'techxal';
$phone = '254715003414';
$key = 'e72777274bc1acd25b753fe33fa1cff00fff2b258ec80db88d7209335d91a27d';
$password = '71645076facbc62f7c5512bcb625180259d9fdd6fa69cbe231329a237823e0fa';

$request = array(
	'credentials' => base64_encode($key.':'.$password),
	'username' => base64_encode($username),
	// 'phone' => base64_encode($phone),
); print_r($request); echo "\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request) );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'cache-control: no-cache',
        'username: '.$username,
        // 'phone: '.$phone,
    )
);
$results = curl_exec($ch);
curl_close ($ch);

if($results):
	$log = '/srv/web/techxal.sys/.config/.techxal.sys-'.$username.'-token.log';
	// $log = '/tmp/techxal.sys-'.$username.'-token.log';
	if(file_put_contents($log,$results)) echo "\nSuccessful\n";
else echo "\nFailed\n";
endif;
