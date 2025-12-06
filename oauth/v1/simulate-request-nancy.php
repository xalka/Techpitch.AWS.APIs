<?php

// require 'config.php';

$url = "http://dev-api.techxal.co.ke/oauth/v1/generate";

$username = 'queenslyn';
$phone = '254725652482';
$key = 'BLGCWIL2MF08FUEDH7DWJ2GVCT5IXTGAQ9AWT54HT2ITDB99RAA75C56547F3F3A6D4587717C73C65F6B1641254968';
$password = '3D7691B608D544C1E0333FC4C4BA7E9B1641254968ENMRKA1FAQKST3E11UPUXFN3OUV3C04OHR1NZD1V4LLA62SZ7HZX3EVQIDTGOKR43XXHUQCAA6LEQUNNFS2ILRYOXWORXIBZFILVKL4MUES2CEMGYYVXZTQ8HLKQBOG8EIINAAMLLJDEWTJSKEDL6O';

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