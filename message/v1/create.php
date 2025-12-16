<?php

$baseDir = dirname(__dir__,2);

require_once $baseDir.'/.config/.config.php'; 
require_once $baseDir.'/.core/.funcs.php'; 

// POST request only
if(!ReqPost()) ReqBad();


$files = ['.mysql.php','.mongodb.php','.procedures.php','KafkaHelper.php'];
foreach ($files as $file) require_once $baseDir.'/.core/'.$file;

$headers = array_change_key_case(getallheaders(),CASE_LOWER);

// Receive json
$req = json_decode(file_get_contents('php://input'),1);

// writeToFile(LOG_FILE,json_encode($req,JSON_UNESCAPED_UNICODE));

$customerId = $headers['customerid'];
$pgroupId = $headers['pgroupid'];
// $alphanumericId = $req['alphanumericId']; // ?? $headers['Alphanumericid'];

$headers = [
    // 'Content-Type: application/json',
    // 'Alphanumericid: '. $alphanumericId,
    // 'Alphanumeric: '. $alphanumeric,
    'customerid: '. $customerId,
    'pgroupid: '. $pgroupId
];

// get alphanumeric
if(isset($req['alphanumericId']) && $req['alphanumericId'] ){
    // if(empty($req['alphanumericId'])){
    //     $req['alphanumericId'] = SDP_ALPHANUMERIC_ID;
    //     $req['alphanumeric'] = SDP_ALPHANUMERIC;

    // } else {
        $url = API_HOST."alphanumeric/v1/view";
        $request = [ 'alphanumericId' => validInt($req['alphanumericId']) ];
        $alphanumeric = json_decode(callAPI('GET',$url,$headers,$request),1);
        if(isset($alphanumeric[0]) && !empty($alphanumeric[0])) $alphanumeric = $alphanumeric[0];

        // if(empty($alphanumeric)){
        //     $req['alphanumericId'] = SDP_ALPHANUMERIC_ID;
        //     $req['alphanumeric'] = SDP_ALPHANUMERIC;
        // } else {
            $req['alphanumericId'] = $alphanumeric['_id'];
            $req['alphanumeric'] = $alphanumeric['title'];
        // }
    // }
} else {
    $req['alphanumericId'] = SDP_ALPHANUMERIC_ID;
    $req['alphanumeric'] = SDP_ALPHANUMERIC;    
}

// $req['typeId'] = isset($headers['Method']) && $headers['Method']=='transaction' ? 1 : 2;
$req['typeId'] = 2;
$req['pgroupId'] = $pgroupId;
$req['customerId'] = $customerId;


// Validate


// 1. Draft [ status = 1 ]
// 2. Scheduled [ status = 2 ]
// 3. Recurring [ status = 3 ]
// 4. Send [ status = 4 ]

switch ($req['status']) {
    //  Draft [ status = 1 ]
    case '1':
        require_once __dir__.'/create-draft.php';
        break;

    //  Scheduled [ status = 2 ]
    case '2':
        require_once __dir__.'/create-schedule.php';
        break;

    //  Scheduled [ Recurring = 3 ]
    case '3':
        require_once __dir__.'/create-recurring.php';
        break;

    //  Send [ status = 4 ]
    case '4':
        require_once __dir__.'/create-send.php';
        break;
    
    default:
        ReqBad();
        break;
}