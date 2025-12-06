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
    'requestId' => '60141234567890',
    'channel' => 'CP',
    'operation' => 'ACTIVATE',
    'OfferCode' => '50120155555',
    'MSISDN' => '254720123456', // Hashed Enabled Service
    'CpId' => '345'
];

$return = callAPI("POST", SDP1.'api/public/SDP/activate', $headers, $request );

print_j($return);
// push to queue

// {
//     "requestId": 17,
//     "channel": "APIGW",
//     "operation": "ACTIVATE",
//     "requestParam": {
//         "data": [
//             {
//                 "name": "OfferCode",
//                 "value": "350032100559"
//             },
//             {
//                 "name": "Msisdn",
//                 "value": "795421629"
//             },
//             {
//                 "name": "Language",
//                 "value": "1"
//             },
//             {
//                 "name": "CpId",
//                 "value": "321"
//             }
//         ]
//     }
// }