<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.config/.funcs.php'; 
// require __dir__.'/../../.config/.mysql.php'; 
// require __dir__.'/../../.config/.mongodb.php';
// require __dir__.'/../../.config/.procedures.php';

// POST request only
if(!ReqPost()) ReqBad();

$headers = [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest',
    //'X-Authorization: '.accessToken()
];

$request = [
    "requestId" =>  17,
    "channel"   =>  "APIGW",
    "operation" =>  "DEACTIVATE",
    "requestParam"  => [
        "data" => [
            [  "name" =>  "OfferCode", "value" =>  "1001" ],
            [  "name" =>  "Msisdn", "value" =>  "254720123456" ],
            [  "name" =>  "CpId", "value" =>  "10" ],
        ]
    ]
];

$return = callAPI("POST", SDP1.'api/public/SDP/deactivate', $headers, $request );

print_j($return);
// push to queue

// {
//     "requestId": "17",
//     "responseId": "10189519962937756186",
//     "responseTimeStamp": "20190924161246",
//     "channel": "3",
//     "sourceAddress": "224.223.10.27",
//     "operation": "DEACTIVATE",
//     "requestParam": {
//             "data": [
//                 {
//                     "name": "OfferCode",
//                     "value": "1001"
//                 },
//                 {
//                     "name": "Msisdn",
//                     "value": "716848648"
//                 },
//                 {
//                     "name": "CpId",
//                     "value": "10"
//                 }
//             ]
//         },
//     "responseParam": {
//     "status": "0",
//     "statusCode": "302",
//     "description": "Dear subscriber,You have cancelled your subscription to LOCAL CHANNEL Pack. Thank you for using our service."
//     }
// }