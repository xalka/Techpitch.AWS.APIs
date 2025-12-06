<?php
function generateToken($url, $data)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    // SSL settings
    curl_setopt($ch, CURLOPT_CAINFO, '/srv/web-data/techpitch/system/sdp/certs/cacert-2024-11-26.pem'); // System CA bundle
    // Uncomment below for testing only:
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest',
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $jsonData = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "Curl error: " . $error_msg;
    }

    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $responseData = json_decode($response, true);

    if ($httpStatusCode === 200 && isset($responseData['token'])) {
        return $responseData['token'];
    } else {
        return "Failed to generate token. HTTP Status: $httpStatusCode. Response: $response";
    }
}


// Usage
$url = "https://dtsvc.safaricom.com:8480/api/auth/login";
$data = [
    "username" => "TechPitchAPI",
    "password" => "Admin@123",
];

$token = generateToken($url, $data);

if ($token) {
    echo "Generated Token: \n";
    print_r($token);
    echo "\n";
} else {
    echo "Token generation failed.\n";
}


/*
Array
(
    [msg] => You have been Authenticated to access this protected API System.
    [token] => eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJUZWNoUGl0Y2hBUEkiLCJhdWQiOiJBQ0NFU1MiLCJzY29wZXMiOiJBRE1JTiIsImlzcyI6Imh0dHA6Ly9zaXhkZWUuY29tIiwiaWF0IjoxNzMzOTk0NTAzLCJleHAiOjE3MzQwMDM1MDN9.8p0Xv3scnLMC2U5sOW7uk2i-oNs3lZIrYUZz1M4JHCyjyAZiFhve9TlB93yBd0BO-bu0B7jEnnZI_RC9eJbe3g
    [refreshToken] => eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJUZWNoUGl0Y2hBUEkiLCJhdWQiOiJSRUZSRVNIIiwic2NvcGVzIjoiQURNSU4iLCJpc3MiOiJodHRwOi8vc2l4ZGVlLmNvbSIsImp0aSI6ImY3MWUyYmE4LWRkNmMtNGVjMi04M2ZkLTEzMTgzYmViNDZkNyIsImlhdCI6MTczMzk5NDUwMywiZXhwIjoxNzQ0Nzk0NTAzfQ.Y8pUo2A2cGxNq87SlLszvrFRE45Xvd2kTXSGMkAxiH6Nbm8hdDzko1IS-wEOKVOwThRhVOvw8WkwJKJ8iunNrQ
)
*/