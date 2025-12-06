<?php

// The URL of the endpoint you are posting to
$url = "https://dtsvc.safaricom.com:8480/api/auth/login";

// Data you want to post
$data = [
    "username" => "TechPitchAPI",
    "password" => "Admin@123",
];

// Convert data to JSON (or use application/x-www-form-urlencoded depending on the API)
$data_string = json_encode($data);

// Path to your certificate files
$client_cert = __dir__.'/../certs/client.crt';
$client_key = __dir__.'/../certs/client.key';
$ca_cert = '/etc/ssl/certs/ca-certificates.crt';

// Initialize cURL session
$ch = curl_init();

// Set the options for the cURL request
curl_setopt($ch, CURLOPT_URL, $url);                  // Set the target URL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);       // Return response as a string
curl_setopt($ch, CURLOPT_POST, true);                 // Use POST method
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  // Attach the POST data

// Set SSL certificates and key
curl_setopt($ch, CURLOPT_SSLCERT, $client_cert);      // Set the client certificate
curl_setopt($ch, CURLOPT_SSLKEY, $client_key);        // Set the client private key
curl_setopt($ch, CURLOPT_CAINFO, $ca_cert);           // Set the CA certificate for server verification

// Disable SSL verification (if you trust the endpoint; otherwise, leave this out or set it to true)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);       // Verify server's SSL certificate
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);          // Verify the host's name in the certificate

// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


// Set the content-type for the request (application/json or other, depending on your API)
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest',
    'Content-Length: ' . strlen($data_string))
);

// Execute the request
$response = curl_exec($ch);

// Check for errors
if(curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    // If successful, handle the response
    echo "Response: " . $response;
}

// Close the cURL session
curl_close($ch);

?>
