<?php

// username: TECHXAL
// password: F4<p8QCA
// system-type: SMPP
// port: 9022


// SMSC: 164.177.157.232
// PORT : 9022
// INTERFACE VESRION:  v3.4
// KeepAlive : Yes

// SERVICE TYPE: NULL
// SOURCE ADDRESS TON: 5
// SOURCE ADDRESS NPI: 0
// DEST ADDRESS TON: 1
// DEST ADDRESS NPI: 1

$host = '164.177.157.232';
$port = 9022;
// $port = 8081;

// Create a new socket
$socket = socket_create(AF_INET, SOCK_STREAM, 0);

// Connect to the server
socket_connect($socket, $host, $port);

$data = [
    'message' => 'Hell Joshua\nWelcome back to Techxal',
    'username' => 'techxal',
    'token' => '2039jd0239dj239d9328dh328dh3s28kd3287d72',
    'time' => time(),
    'contacts' => ['254715003414']
];

// Write data to the server
socket_write($socket, json_encode($data));

// Read data from the server
$data = socket_read($socket, 1024);

echo "Received from server: $data\n";

// Close the connection
socket_close($socket);
