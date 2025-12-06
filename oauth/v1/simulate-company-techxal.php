<?php

$key = 'e72777274bc1acd25b753fe33fa1cff00fff2b258ec80db88d7209335d91a27d';
$password = '71645076facbc62f7c5512bcb625180259d9fdd6fa69cbe231329a237823e0fa';

$headers = [
    'Authorization: '.base64_encode($key.':'.$password),
    'Content-Type: application/json',
    'Accept: application/json',
    'cache-control: no-cache'
];

$request = [ 
    'reference' => 9082930632194697
];

$url = "http://dev-api.techxal.co.ke/oauth/v1/generate";
exit('Recheck');

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request) );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$results = curl_exec($ch);
curl_close ($ch);
print_r($results);
if($results):
	$log = '/srv/web/techxal.sys/.config/.techxal.sys-techxal-token.log';
	// $log = '/tmp/techxal.sys-'.$username.'-token.log';
	if(file_put_contents($log,$results)){
        echo "\nSuccessful\n";
    }
else echo "\nFailed\n";
endif;