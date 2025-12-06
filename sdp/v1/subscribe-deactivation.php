<?php

// prevent from being access via 
if(php_sapi_name() != 'cli') die('Access denied.');

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.redis.php';

$token = json_decode(redisGetValue(SDP_TOKEN),1);

$headers = [
  'Content-Type: application/json',
  'Accept: application/json',
  'X-Requested-With: XMLHttpRequest',
  'X-Authorization: Bearer '.$token
];

$request = [
    "requestId" =>  time(),
    "channel"   =>  "SMS",
    "operation" =>  "DEACTIVATE",
    "requestParam"  =>  [
        "data"  =>  [
            ["name" =>  "Msisdn", "value" =>  "254715003414"],
            ["name" =>  "OfferCode", "value" =>  "001075102385"],
            ["name" =>  "CpId", "value" =>  SDP_ID],
        ]
    ]
];

$return = callAPI("POST", SDP1.'api/public/SDP/deactivate', $headers, $request);

print_r($return);


/*
{
  "requestId": "17",
  "responseId": "10189519962937756186",
  "responseTimeStamp": "20190924161246",
  "channel": "3",
  "sourceAddress": "224.223.10.27",
  "operation": "DEACTIVATE",
  "requestParam": {
    "data": [
      {
        "name": "OfferCode",
        "value": "1001"
      },
      {
        "name": "Msisdn",
        "value": "716848648"
      },
      {
        "name": "CpId",
        "value": "10"
      }
    ]
  },
  "responseParam": {
    "status": "0",
    "statusCode": "302",
    "description": "Dear subscriber, you have cancelled your subscription to LOCAL CHANNEL Pack. Thank you for using our service."
  }
}
*/