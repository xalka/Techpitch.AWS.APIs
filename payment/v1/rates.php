<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.procedures.php';

$headers = getallheaders();

// if(isset($headers['CustomerId'])) $dbdata['customerId'] = validInt($headers['Customerid']);
// if(isset($headers['Groupid'])) $dbdata['groupId'] = validInt($headers['Groupid']);

// GET request only
if(ReqGet()){
    // validate

    $filter = [
        // 'groupId' => validInt($headers['Groupid'])
        // 'views' => [
        //     '$gte' => 100,
        // ],
    ];

    if(isset($_GET['id'])) $filter['_id'] = validInt($_GET['id']);
    
    $options = [
        'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
        'limit' => isset($_GET['limit']) ? $_GET['limit'] : 20,
        'sort' => ['_id' => 1],
        'projection' => [
            '_id'=>1, 'from'=>1, 'to'=>1, 'price'=>1, 'created'=>1
        ],	
    ];
    
    print_j(mongoDateTime(mongoSelect(CRATES,$filter,$options)));

} elseif(ReqPost()){
    // 2. validate

    // 3. read from mysql
    $dbdata = [
        'action' => 5,
        'limit' => isset($_GET['limit']) ? validInt($_GET['limit']) : LIMIT
    ];

    $payments = PROC(PAYMENT($dbdata));
    if(!empty($payments)) $payments = $payments[0];

    $payments = array_map(function($payment) {
        $timestamp = strtotime($payment['created']);
        $payment['date'] = date('Y-M-d', $timestamp);
        $payment['time'] = date('H:i', $timestamp);
        return $payment;
    }, $payments);


    print_j($payments);


} ReqBad();