<?php

// require 'config.php';

$url = "http://dev-api.techxal.co.ke/oauth/v1/generate";

$username = 'techxal';
$phone = '254715003414';
$key = 'e72777274bc1acd25b753fe33fa1cff00fff2b258ec80db88d7209335d91a27d';
$password = '71645076facbc62f7c5512bcb625180259d9fdd6fa69cbe231329a237823e0fa';

$request = array(
	'credentials' => base64_encode($key.':'.$password),
	// 'username' => base64_encode($username),
	'phone' => base64_encode($phone),
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request) );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'cache-control: no-cache',
        // 'username: '.$username,
        'phone: '.$phone,
    )
);
$results = curl_exec($ch);
curl_close ($ch);

file_put_contents('/tmp/techxal.sys-'.$phone.'-token.log',$results);

// $file = '/tmp/techxal.sys-'.$phone.'-token.log';

// $handle = fopen($file,'w') or die('Unable to open file');
// fwrite($handle,$results);
// fclose($handle);

print_r($results);

// if (file_exists($file)) {
//     file_put_contents($file, date('H:i:s')." : $data \n\n", FILE_APPEND);
// } else {
//     file_put_contents($file, date('H:i:s')." : $data \n\n");
// }