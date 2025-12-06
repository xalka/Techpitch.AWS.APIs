<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';

$headers = getallheaders();

$filter = [];

if(isset($headers['Groupid'])) $filter['groupId'] = validInt($headers['Groupid']);
if(isset($_GET['alphanumericId'])) $filter['_id'] = validInt($_GET['alphanumericId']);

if(isset($_GET['title'])) $filter['title'] = ['$regex' => validString($_GET['title']), '$options' => 'i'];
if(isset($_GET['customer'])){
    // $customer = validString($_GET['customer']);
    $customer = ['$regex' => validString($_GET['customer']), '$options' => 'i'];
    $filter['$or'] = [
        ['fname' => $customer],
        ['lname' => $customer],
        ['cname' => $customer]
    ];    
}

$options = [
	'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
	'limit' => isset($_GET['limit']) ? $_GET['limit'] : LIMIT,
	'sort' => ['_id' => -1],
    'projection' => [
        '_id'=>1, 'title'=>1, 'cname'=>1, 'fname'=>1, 'lname'=>1, 'active'=>1, 'groupId'=>1, 'created'=>1
    ],
];

print_j(mongoDateTime(mongoSelect(CALPHANUMERIC,$filter,$options)));