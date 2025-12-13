<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
// require __dir__.'/../../.config/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

$headers = array_change_key_case(getallheaders(), CASE_LOWER);

// GET request only
if(ReqGet()){

    // 1. Receive $_GET
    // print_r($_GET);

    // 2. validate

    // 3. read from mysql
    $dbdata = [
        'action' => 6,
        'adminId' => validInt($headers['customerid']),
        'pgroupId' => validInt($headers['pgroupid'])
    ];
    $balance = PROC(PAYMENT($dbdata));
    if(!empty($balance)) $balance = $balance[0][0];
    print_j($balance);

} elseif(ReqPost()){
    $req = json_decode(file_get_contents('php://input'),1);
    $dbdata = [
        'action' => 7,
        'units' => validInt($req['units']),
        'adminId' => validInt($headers['customerid']),
        'pgroupId' => validInt($headers['pgroupid'])
    ];
    print_j(PROC(PAYMENT($dbdata))[0][0]);
    // if(isset($balance[0]) && !empty($balance[0])) $balance = $balance[0];
    // print_j($balance);

} else ReqBad();