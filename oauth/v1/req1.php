<?php

require __dir__.'/.config.php';

$url = 'https://api.africastalking.com/auth-token/generate';
$key = 'e72777274bc1acd25b753fe33fa1cff00fff2b258ec80db88d7209335d91a27d';
$key = '71645076facbc62f7c5512bcb625180259d9fdd6fa69cbe231329a237823e0fa';
$username = 'techxalSMS';

$data = array(
    'apiKey' => $key,
    'username' => $username,
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data) );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'apiKey: '.$key,
        'cache-control: no-cache'
    )
);
$results = curl_exec($ch);
curl_close ($ch);

if(file_put_contents(AUTHFILE,$results)) echo "\nSuccessful\n";
else echo "\nUnsuccessful\n";

exit;

$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
// $key = 'iO2IccplpwakSPhNrXSIP5pFOl2t63Qm';
// $secret = 'BPcOJQGoDHxinC0K';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
$credentials = base64_encode(KEY.':'.SECRET);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials)); //setting a custom header
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data) );
$response = curl_exec($curl);
curl_close($curl);

if(file_put_contents(AUTHFILE,$response)) echo "\nSuccessful\n";
else echo "\nUnsuccessful\n";