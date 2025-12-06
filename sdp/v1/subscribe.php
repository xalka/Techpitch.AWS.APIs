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
    "requestId" =>  'TP-'.time(),
    "channel"   =>  "SMS",
    "operation" =>  "ACTIVATE",
    "requestParam"  =>  [
        "data"  =>  [
            ["name" =>  "OfferCode", "value" =>  "001075102385"], // 001075102385 // 001075102371 
            ["name" =>  "Msisdn", "value" =>  "254715003414"],
            ["name" =>  "Language", "value" =>  "1"],
            ["name" =>  "CpId", "value" => SDP_ID], 
        ]
    ]
];

$return = callAPI("POST", SDP1.'api/public/SDP/activate', $headers, $request);

print_r($return);


/*

-------- success  
{
  "requestId": "17",
  "responseId": "cp2910183038077087336761",
  "responseTimeStamp": "20191104092806",
  "channel": "SMS",
  "operation": "ACTIVATE",
  "requestParam": {
    "data": [
      {
        "name": "OfferCode",
        "value": "350032100559"
      },
      {
        "name": "Msisdn",
        "value": "795421629"
      },
      {
        "name": "Language",
        "value": "1"
      },
      {
        "name": "CpId",
        "value": "321"
      }
    ]
  },
  "responseParam": {
    "status": "0",
    "statusCode": "0816",
    "description": "Thank you, your activation of service 5000_Promotional is not processed."
  }
}

-------- failed  
{
  "requestId": "TP-1742274626",
  "responseId": "techpitchltd10185454014943721889",
  "responseTimeStamp": "20250318081027",
  "channel": "SMS",
  "operation": "ACTIVATE",
  "requestParam": {
    "data": [
      {
        "name": "OfferCode",
        "value": "001075102371"
      },
      {
        "name": "Msisdn",
        "value": "254715003414"
      },
      {
        "name": "Language",
        "value": "1"
      },
      {
        "name": "CpId",
        "value": "751"
      }
    ]
  },
  "responseParam": {
    "status": "1",
    "statusCode": "100",
    "description": "Feature is not available"
  }
}


------ failed 
{
  "requestId": "TP-1742274828",
  "responseTimeStamp": "20250318081349",
  "channel": "SMS",
  "operation": "ACTIVATE",
  "requestParam": {
    "data": [
      {
        "name": "OfferCode",
        "value": "001075102385"
      },
      {
        "name": "Msisdn",
        "value": "254715003414"
      },
      {
        "name": "Language",
        "value": "1"
      },
      {
        "name": "CpId",
        "value": "751"
      }
    ]
  },
  "responseParam": {
    "status": "1",
    "statusCode": "11",
    "description": "Offer does not exists"
  }
}


*/