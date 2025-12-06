<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';

if(!ReqGet()) ReqBad();

if(!isset($_GET['id']) || empty($_GET['id'])) ReqBad();

// 1. Receive $_GET
$headers = getallheaders();

if(!isset($headers['Groupid']) && empty($headers['Groupid'])) ReqBad(); 

$pgroupId = validInt($headers['Groupid']);

$start = (int)$_GET['start'] ?? 0;
$limit = (int)$_GET['limit'] ?? 10; 
$skip = $start*$limit;

$pipeline = [
    [
        '$match' => [
            '_id' => validInt($_GET['id']),
            'pgroupId' => $pgroupId
        ]
    ],
    [
        '$facet' => [
            "metadata" => [
                [
                    '$project' => [
                        '_id' => 1,
                        'title' => 1,
                        'active' => 1,
                        'created' => ['$dateToString' => ['format' => "%Y-%m-%d %H:%M:%S", 'date' => '$created']]
                    ]
                ]
            ],                
            "contacts" => [
                ['$unwind' => '$contacts'],
                ['$skip' => $skip],
                ['$limit' => $limit],              
                [
                    '$project' => [
                        '_id' => 0,
                        'phone' => '$contacts.phone',
                        'fname' => ['$ifNull' => ['$contacts.fname', '']],
                        'lname' => ['$ifNull' => ['$contacts.lname', '']]
                    ]
                ]
            ]           
        ]
    ]
];

print_j(mongoDateTime(mongoAggregate(CGROUP,$pipeline)));