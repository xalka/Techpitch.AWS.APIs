<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

if(!ReqPut()) ReqBad();

$headers = getallheaders();

$req = json_decode(file_get_contents('php://input'),1);
print_r($req); exit;
foreach ($req['contacts'] as $key => $contact) {
    $req['contacts'][$key]['phone'] = validPhone($contact['phone']);
}

$dbdata = [
    'action' => 1,
    'customerId' => $headers['Customerid'],
    'pgroupId' => $headers['Groupid'],
    'title' => $req['title'],
    // 'message' => $req['message']
];