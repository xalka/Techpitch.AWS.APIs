<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.procedures.php';

// $headers = getallheaders();

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


} 

ReqBad();


/*
function calculateSmsQuantity(float $amount, array $tiers): int {
    $remaining = $amount;
    $totalSms = 0;

    foreach ($tiers as $tier) {
        $tierSmsCount = $tier['end'] - $tier['start'] + 1;
        $tierCost = $tierSmsCount * $tier['rate'];

        if ($remaining >= $tierCost) {
            // Can buy entire tier
            $totalSms += $tierSmsCount;
            $remaining -= $tierCost;
        } else {
            // Partial tier
            $smsInTier = floor($remaining / $tier['rate']);
            $totalSms += $smsInTier;
            $remaining = 0;
            break;
        }
    }
    return $totalSms;
}

// Usage
$smsTiers = [
    ['start' => 1,       'end' => 100000,  'rate' => 0.50],
    ['start' => 100001,  'end' => 500000,  'rate' => 0.40],
    ['start' => 500001,  'end' => 1000000, 'rate' => 0.35],
];

$amount = 1000; // KES
echo "\nSMS Quantity for {$amount} KES: " . calculateSmsQuantity($amount, $smsTiers) . "\n";

$amount = 100005; // KES
echo "SMS Quantity for {$amount} KES: " . calculateSmsQuantity($amount, $smsTiers) . "\n";

$amount = 500005; // KES
echo "SMS Quantity for {$amount} KES: " . calculateSmsQuantity($amount, $smsTiers) . "\n";
*/