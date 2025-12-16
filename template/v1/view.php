<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';

// GET request only
if(!ReqGet()) ReqBad();

if(!isset(HEADERS['pgroupid'])) ReqBad();

$filter = [
    'groupId' => validInt(HEADERS['pgroupid'])
];

if(isset($_GET['id'])) $filter['_id'] = validInt($_GET['id']);

if (isset($_GET['title']) && !empty($_GET['title'])){
    $filter['title'] = ['$regex' => validString($_GET['title']), '$options' => 'i'];
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
	'sort' => ['_id' => 1],
    'projection' => [
        '_id'=>1, 'title'=>1, 'message'=>1, 'strlen' => 1, 'created'=>1
    ],	
];

print_j(mongoDateTime(mongoSelect(CTEMPLATE,$filter,$options)));