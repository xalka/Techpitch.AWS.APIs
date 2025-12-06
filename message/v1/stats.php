<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';

// 1. Receive $_GET

// 1. Receive $_GET
$headers = getallheaders();

$pgroupId = validInt($headers['Groupid']);

// Aggregation pipeline
$pipeline = [
    [
        '$match' => [
            'pgroupId' => $pgroupId,
            'created' => [ '$gte' => mongodate('-12 months') ]
        ]
    ],
    [
        '$group' => [
            '_id' => [
                '$dateToString' => [
                    'format' => '%Y-%m',
                    'date' => '$created'
                ]
            ],
            'units' => ['$sum' => '$units']
        ]
    ],
    [
        '$sort' => ['_id' => 1]
    ]
];

$stats = mongoAggregate(CMESSAGE,$pipeline);

print_j($stats);