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
    "operation" =>  "SendSMS",
    "requestParam"  =>  [
        "data"  =>  [
            ["name" =>  "LinkId", "Value" =>  "00010310189519161781865526"],
            ["name" =>  "Msisdn", "Value" =>  "254795421629"],
            ["name" =>  "Content", "Value" =>  "Thank You for Ondemand Subscription SAFRI TEST TUN Subscption test Send sms"],
            ["name" =>  "OfferCode", "Value" =>  "1003"],
            ["name" =>  "CpId", "Value" =>  "10"],
        ]
    ]
];

$return = callAPI("POST", SDP1.'api/public/SDP/sendSMSRequest', $headers, $request);

print_j($return);
// save to redis

// {
//   "requestId": "17",
//   "responseId": "10189519182688287792",
//   "responseTimeStamp": "20190924155948",
//   "channel": "3",
//   "sourceAddress": "224.223.10.27",
//   "operation": "SendSMS",
//   "requestParam": {
//     "data": [
//       {
//         "name": "LinkId",
//         "value": "00010310189519161781865526"
//       },
//       {
//         "name": "Msisdn",
//         "value": "254795421629"
//       },
//       {
//         "name": "Content",
//         "value": "Thank You for Ondemand Subscription SAFRI TEST TUN Subscption test Send sms"
//       },
//       {
//         "name": "OfferCode",
//         "value": "1003"
//       },
//       {
//         "name": "CpId",
//         "value": "10"
//       }
//     ]
//   },
//   "responseParam": {
//     "status": "1",
//     "statusCode": "782",
//     "description": "Send SMS Failed"
//   }
// }