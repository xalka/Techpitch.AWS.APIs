<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';

// 1. Receive $_GET

// 1. Receive $_GET
$headers = getallheaders();

$filter = [];

if(isset($_GET['id'])) $filter['_id'] = validInt($_GET['id']);

$options = [
	'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
	'limit' => isset($_GET['limit']) ? $_GET['limit'] : LIMIT,
	'sort' => ['_id' => 1],
    /* Only return the following fields in the matching documents */
    'projection' => [
        '_id'=>1, 'status'=>1, 'created'=>1
    ],
];

print_j(mongoDateTime(mongoSelect(CMSTATUS,$filter,$options)));