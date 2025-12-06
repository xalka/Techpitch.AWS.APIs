<?php

function phonenumber($i){
	// Generate a random number between 10000000 and 99999999
	$randomNumber = mt_rand(10000000+$i, 99999999);
	// Convert the random number to a string
	$randomString = strval($randomNumber);
	// If the random number is less than 8 characters, pad it with leading zeros
	$randomString = str_pad($randomString, 8, "0", STR_PAD_LEFT);
	// Output the random string
	return $randomString;
}

$host = '10.26.11.14';
$port = 8443;
// $port = 8081;

// Create a new socket
$socket = socket_create(AF_INET, SOCK_STREAM, 0);

// Connect to the server
socket_connect($socket, $host, $port);

while (true) {
	$data = [
		'message' => 'Create the file sendsms.py containing the code below. Replace the hostname smscsim.melroselabs.com, port 2775, SYSTEMID and PASSWORD values with those from your SMPP API account. Alternatively, replace SYSTEMID and PASSWORD with those allocated to you for use with the Melrose Labs SMSC Simulator.',
		'username' => 'techxal',
		'token' => '2039jd0239dj239d9328dh328dh3s28kd3287d72',
		'time' => time(),
		'contacts' => []
	];
	for ($i=0; $i < 10; $i++) {
		$data['contacts'][] = phonenumber($i);
	}

	// Write data to the server
	socket_write($socket, json_encode($data));

	// Read data from the server
	$data = socket_read($socket, 1024);

	echo "Received from server: $data\n";

	sleep(2);
}

// Close the connection
socket_close($socket);
