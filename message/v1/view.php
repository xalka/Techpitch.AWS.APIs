<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';

$headers = getallheaders();

$filter = [];

if(isset($headers['Groupid'])) $filter['pgroupId'] = validInt($headers['Groupid']);

if(isset($_GET['id']) && !empty($_GET['id'])) $filter['_id'] = validInt($_GET['id']);
if(isset($_GET['statusId']) && !empty($_GET['statusId'])) $filter['statusId'] = validInt($_GET['statusId']);

if (isset($_GET['title']) && !empty($_GET['title'])) {
    $filter['title'] = [ '$regex' => validString($_GET['title']),  '$options' => 'i'  ];
}

if (isset($_GET['startdate']) && !empty($_GET['startdate'])) {
    $filter['created']['$gte'] = mongodate($_GET['startdate']);
}

if (isset($_GET['enddate']) && !empty($_GET['enddate'])) {
    $filter['created']['$lte'] = mongodate($_GET['enddate']);
}

$options = [
	'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
	'limit' => isset($_GET['limit']) ? $_GET['limit'] : LIMIT,
	'sort' => ['_id' => -1],
    'projection' => [
        '_id'=>1, 'mode'=>1, 'title'=>1, 'message'=>1, 'alphanumeric'=>1, 'transactionId'=>1, 'units'=>1, 'recipients'=>1, 'status'=>1, 'statusId'=>1, 'sent'=>1, 'created'=>1, 'scheduled'=>1, 'contactGroupId'=>1
    ],
];

print_j(mongoDateTime(mongoSelect(CMESSAGE,$filter,$options)));