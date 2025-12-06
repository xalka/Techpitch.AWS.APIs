<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
// require __dir__.'/../../.config/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

$headers = getallheaders();

// GET request only
if(ReqGet()){

    // 1. Receive $_GET
    // print_r($_GET);

    // 2. validate

    // 3. read from mysql
    $dbdata = [
        'action' => 13,
        // 'adminId' => validInt($headers['Customerid']),
        'groupId' => validInt($headers['Groupid']),
        'statusId' => 3
    ];
    $balance = PROC(PAYMENT($dbdata));
    if(!empty($balance)) $balance = $balance[0][0];
    print_j($balance);

} else ReqBad();