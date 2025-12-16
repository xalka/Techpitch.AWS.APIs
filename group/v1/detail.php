<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';

if(!ReqGet()) ReqBad();

if(!isset($_GET['groupId']) || empty($_GET['groupId'])) ReqBad();

$headers = array_change_key_case(getallheaders(), CASE_LOWER);

if(!isset($headers['pgroupid']) && empty($headers['pgroupid'])) ReqBad(); 

// $pgroupId = validInt($headers['pgroupid']);

$filter = [
    'groupId' => validInt($_GET['groupId']),
    'pgroupId' => validInt($headers['pgroupid'])
];

// if(isset($headers['pgroupId'])) $filter['pgroupId'] = $pgroupId;

// if(isset($_GET['id']) && !empty($_GET['id'])) $filter['_id'] = validInt($_GET['id']);
// if(isset($_GET['statusId']) && !empty($_GET['statusId'])) $filter['statusId'] = validInt($_GET['statusId']);

// if (isset($_GET['title']) && !empty($_GET['title'])) {
//     $filter['title'] = [ '$regex' => validString($_GET['title']),  '$options' => 'i'  ];
// }

// if (isset($_GET['startdate']) && !empty($_GET['startdate'])) {
//     $filter['created']['$gte'] = mongodate($_GET['startdate']);
// }

// if (isset($_GET['enddate']) && !empty($_GET['enddate'])) {
//     $filter['created']['$lte'] = mongodate($_GET['enddate']);
// }

// print_r($filter); exit;

$options = [
	'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
	'limit' => isset($_GET['limit']) ? $_GET['limit'] : LIMIT,
	// 'sort' => ['_id' => -1],
    // 'projection' => [
    //     '_id'=>1, 'phone'=>1, 'fname'=>1, 'lname'=>1, 'pgroupId'=>1, 'groupId'=>1, 'active'=>1, 'created'=>1
    // ],
];

// print_r( mongoSelect(GCONTACT,$filter,$options) ); exit;

print_j(mongoDateTime(mongoSelect(GCONTACT,$filter,$options)));