<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

// POST request only
if(!ReqPost()) ReqBad();

// 1. Receive json
$headers = array_change_key_case(getallheaders(), CASE_LOWER);

$dbdata = [
    'action' => 12,
    // 'rate' => $req['rate'],
    'amount' => validInt($req['amount']),
    'adminId' => validInt($headers['adminid']),
    // 'reference' => validString($req['reference']),
    'pgroupId' => validInt($req['pgroupId']),
    'modeId' => validInt($req['modeId']),
    'statusId' => 3
];

if(isset($req['reference']) && !empty($req['reference'])) $dbdata['reference'] = validString($req['reference']);
if(isset($req['rates']) && !empty($req['rates'])) $dbdata['rates'] = validInt($req['rates']);

try {
    // 2. save the request into database
    $return = PROC(PAYMENT($dbdata));

    if(!empty($return) && $return[0][0]['created']){
        // update account units in mongoDb
        // $request = [
        //     'units' => $return[0][0]['units'],
        //     'accountId' => $dbdata['groupId']
        // ];
        // create account
        $filter = [
            '_id' => $dbdata['groupId']
        ];
        $dbdata = [
            'units' => $return[0][0]['units'],
            "updatedAt" => mongodate('NOW')
        ];
        $return1 = mongoUpdate(CACCOUNT,$filter, $dbdata);
        // $return1 = mongoInsert(CACCOUNT,$dbdata);
        
        // if($return3);

        $response = [
            'status' => 201, 
            'paymentId' => $return[0][0]['id']
        ]; 
    } else {
        $response = [
            'status' => 401,
            'error' => 'Unable to create payment'
        ];
    }
    

} catch (Exception $e) {
    $response = [
        'status' => 401,
        'error' => $e->getMessage()
    ];    
}

// 7. Respond with a json
print_j($response);