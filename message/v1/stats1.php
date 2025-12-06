<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';

// 1. Receive $_GET

// 1. Receive $_GET
$headers = getallheaders();

$filter = [];

/*
$pipeline = [
    [
        '$match' => [
            // 'created' => [ '$gte' => mongodate('-12 months'), '$lte' => mongodate('NOW') ],
            'created' => [ '$gte' => mongodate('-12 months')  ],
        ]
    ],
    [
        '$group' => [
            '_id' => [
                // 'year'  => [ '$year' => '$created' ],
                'month' => [ '$month' => '$created' ]
            ],
            // 'total_sms' => [ '$sum' => '$recipients' ]
            'total_sms' => [ '$sum' => '$statusId' ]
        ]
    ],
    [
        '$sort' => [ '_id.month' => 1 ]
    ]
];

$command = new MongoDB\Driver\Command([
    'aggregate' => 'message',
    'pipeline' => $pipeline,
    'cursor' => new stdClass(),
]);
*/


/*
$collection = (new MongoDB\Client)->your_database->message;

$now = new DateTime();
$lastYear = (clone $now)->modify('-11 months')->modify('first day of this month')->setTime(0, 0);

$pipeline = [
    ['$match' => [
        'created' => ['$gte' => new MongoDB\BSON\UTCDateTime($lastYear->getTimestamp() * 1000)]
    ]],
    ['$group' => [
        '_id' => [
            '$dateToString' => [
                'format' => '%Y-%m',
                'date' => '$created'
            ]
        ],
        'totalUnits' => ['$sum' => '$units']
    ]],
    ['$sort' => ['_id' => 1]]
];

$result = $collection->aggregate($pipeline);

// Format the result into a PHP array
$data = iterator_to_array($result);
*/



$now = new DateTime();
$start = (clone $now)->modify('-11 months')->setDate($now->format('Y'), $now->format('m'), 1)->setTime(0, 0, 0);

// Convert to MongoDB UTCDateTime
$startDate = new MongoDB\BSON\UTCDateTime($start->getTimestamp() * 1000);

// Aggregation pipeline
$pipeline = [
    [
        '$match' => [
            'created' => [ '$gte' => $startDate ]
        ]
    ],
    [
        '$group' => [
            '_id' => [
                // 'year'  => [ '$year' => '$created' ],
                'month' => [ '$month' => '$created' ]
            ],
            'total_sms' => [ '$sum' => '$recipients' ]
        ]
    ],
    [
        '$sort' => [ '_id.month' => 1 ]
    ]
];

$command = new MongoDB\Driver\Command([
    'aggregate' => 'message',
    'pipeline' => $pipeline,
    'cursor' => new stdClass(),
]);


$stats = mongoAggregate($command);

print_r($stats);