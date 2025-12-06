<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';

if(!ReqGet()) ReqBad();

$headers = getallheaders();

if(!isset($headers['Groupid']) && empty($headers['Groupid'])) ReqBad(); 

$pgroupId = validInt($headers['Groupid']);

$match = ['pgroupId' => $pgroupId];

if (isset($_GET['id']) && !empty($_GET['id'])){
    $match['_id'] = validInt($_GET['id']);
}

if (isset($_GET['title']) && !empty($_GET['title'])){
    $match['title'] = ['$regex' => validString($_GET['title']), '$options' => 'i'];
}

if (isset($_GET['startdate']) && !empty($_GET['startdate'])) {
    $match['created']['$gte'] = mongodate($_GET['startdate']);
}

if (isset($_GET['enddate']) && !empty($_GET['enddate'])) {
    $match['created']['$lte'] = mongodate($_GET['enddate']);
}

if (isset($_GET['phone']) && !empty($_GET['phone'])){
    $match['contacts'] = [
        '$elemMatch' => [
            'phone' => validPhone($_GET['phone'])
        ]
    ];    
}

if (isset($_GET['name']) && !empty($_GET['name'])){
    $name = validString($_GET['name']);
    $match['contacts'] = [
        '$elemMatch' => [
            '$or' => [
                ['fname' => ['$regex' => $name, '$options' => 'i']],
                ['lname' => ['$regex' => $name, '$options' => 'i']]
            ]
        ]
    ];
}

$pipeline = [
    ['$match' => $match ],
    ['$sort' => ['_id' => -1]],
    ['$skip' => isset($_GET['start']) ? (int)$_GET['start'] : 0],
    ['$limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 10],
    ['$project' => [
            '_id' => 1,
            'pgroupId' => 1,
            'title' => 1,
            'created' => 1,
            'active' => 1,
            'count' => ['$size' => ['$ifNull' => ['$contacts', []]]]
        ]
    ]
];

print_j(mongoDateTime(mongoAggregate(CGROUP,$pipeline)));