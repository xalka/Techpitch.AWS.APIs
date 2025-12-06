<?php

// Ensure whitelisting of sdp ips

require_once __dir__.'/../../.config/.config.php'; 
require_once __dir__.'/../../.core/.funcs.php'; 

require_once __dir__.'/../../.core/.mysql.php'; 
require_once __dir__.'/../../.core/.procedures.php';
require_once __dir__.'/../../.core/.mongodb.php';

if(!ReqPost()) ReqBad();

// {"requestId": "1740487593180","requestTimeStamp": "2025-02-25 15:46:33","channel": "SMS","operation": "DeliveryReceipt","traceID": "7837$195233235573068077","requestParam": {"data": [{"name": "Msisdn","value": "254715003414"},{"name": "CpId","value": "102126"},{"name": "correlatorId","value": "Tp1740487573"},{"name": "Description","value": "DeliveredToTerminal"},{"name": "deliveryStatus","value": "0"},{"name": "Type","value": "DELIVER_RECEIPT(Bulk)"},{"name": "campaignId","value": "7837"}]}}

$results = file_get_contents('php://input');

writeToFile(SDP_LOG,$results);

// 1. Queue to kafka

// 2. Process from kafka

// 3. Update mysql
    // 1. Update the message
    // 2. Update each number if delivered, block and pending
    // 3. If blocked, update the group with numbers blocked per alphanumeric

// 4. Insert & Update mongodb

$results = json_decode($results,1); 

if(isset($results['operation'])):

    switch (strtolower($results['operation'])) {

        // bulk sms
        case 'deliveryreceipt'://  print_r($results['requestParam']['data']); exit;
            $dbdata = [
                'action' => 5,
                'statusId' => 6,
                'sentAt' => $results['requestTimeStamp'],
            ];
            foreach ($results['requestParam']['data'] as $value) { 
                if($value['name'] === 'Msisdn') $dbdata['phone'] = validPhone($value['value']);
                if($value['name'] === 'correlatorId') $dbdata['messageId'] = $value['value'];
            }

            // print_r($dbdata);
            $dlr = PROC(Message($dbdata))[0][0];

            $dlr['_id'] = validInt($dlr['recipientId']);
            $dlr['messageId'] = validInt($dlr['messageId']);
            $dlr['blocked'] = validInt($dlr['blocked']);
            $dlr['groupId'] = validInt($dlr['groupId']);
            $dlr['created'] = mongodate($dlr['created']);
            $dlr['delivered'] = mongodate($dlr['delivered']);

            // unset($dlr['recipientId']);
            // unset($dlr['sentAt']);
            // unset($dlr['deliveredAt']);
            // unset($dlr['updated']);
            
            $dlr = array_diff_key($dlr, array_flip(['recipientId', 'updated']));

            print_r(mongoInsert(CDLR,$dlr));

            break;

        // ussd prompt
        // ACTIVATION: subscriber subscribes to an offer.
        // DEACTIVATION: subscriber unsubscribes from an offer.
        // DELIVERY_RECEIPT:send sms request
        case 'CP_NOTIFICATION':
            $results['operation'] = 'CP_NOTIFICATION';
            break;

        // receiving MO sms
        case 'INTERACTIVE':
            $results['operation'] = 'INTERACTIVE';
            break;

        default:
            $results['operation'] = 'Unknown';
            break;
    }

endif;