<?php
/*
{
    "recipients": [
        {
            "email": "joshua@techxal.com",
            "name": "Joshua Musyoka"
        }
    ],
    // "sender": {
    //     "email": "info@techxal.com",
    //     "name": "Techxal Limited"
    // },
    "subject": "TechPitch Account Activation",
    "content": "Welcome to TechPitch, your verification code is 8373933"
}
*/

$url = "https://www.techxal.com/sendemail.php";

$json = file_get_contents("php://input");

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo "cURL error: " . curl_error($ch);
}
curl_close($ch);

header('Content-Type: application/json');
echo $response;