<?php

require_once __dir__.'/../../.config/.config.php'; 
require_once __dir__.'/../../.core/.funcs.php'; 

// POST request only
if(!ReqPost()) ReqBad();

require_once __dir__.'/../../.core/.mysql.php'; 
require_once __dir__.'/../../.core/.mongodb.php';
require_once __dir__.'/../../.core/.procedures.php';
require_once __dir__.'/../../.core/Kafka/KafkaClient.php';

$headers = getallheaders();

// Receive json
$req = json_decode(file_get_contents('php://input'),1);

writeToFile(LOG_FILE,json_encode($req,JSON_UNESCAPED_UNICODE));

$customerId = $headers['Customerid'];
$pgroupId = $headers['Groupid'];
// $alphanumericId = $req['alphanumericId']; // ?? $headers['Alphanumericid'];

$headers = [
    'Content-Type: application/json',
    // 'Alphanumericid: '. $alphanumericId,
    // 'Alphanumeric: '. $alphanumeric,
    'Customerid: '. $customerId,
    'Groupid: '. $pgroupId
];

// get alphanumeric
if(isset($req['alphanumericId']) && $req['alphanumericId'] ){
    // if(empty($req['alphanumericId'])){
    //     $req['alphanumericId'] = SDP_ALPHANUMERIC_ID;
    //     $req['alphanumeric'] = SDP_ALPHANUMERIC;

    // } else {
        $url = API_HOST."alphanumeric/v1/view";
        $request = [ 'alphanumericId' => validInt($req['alphanumericId']) ];
        $alphanumeric = json_decode(callAPI('GET',$url,$headers,$request),1); // print_r($alphanumeric[0]['title']);

        // if(empty($alphanumeric)){
        //     $req['alphanumericId'] = SDP_ALPHANUMERIC_ID;
        //     $req['alphanumeric'] = SDP_ALPHANUMERIC;
        // } else {
            $req['alphanumericId'] = $alphanumeric[0]['_id'];
            $req['alphanumeric'] = $alphanumeric[0]['title'];
        // }
    // }
} else {
    $req['alphanumericId'] = SDP_ALPHANUMERIC_ID;
    $req['alphanumeric'] = SDP_ALPHANUMERIC;    
}

$req['typeId'] = isset($headers['Method']) && $headers['Method']=='transaction' ? 1 : 2;
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