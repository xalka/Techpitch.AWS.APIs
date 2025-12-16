<?php

// Ensure whitelisting of sdp ips

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';

// if(!ReqPost()) ReqBad();

$results = file_get_contents('php://input');

writeToFile(LOG_SDP,$results);

$results = json_decode($results,1);

print_r($results);