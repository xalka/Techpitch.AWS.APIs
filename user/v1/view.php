<?php

// 1. Receive json

// 2. Validate

// 3. Filter

// 4. Read from mongodb



require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
// require __dir__.'/../../.config/.mysql.php'; 
require __dir__.'/../../.core/.mongodb.php';
// require __dir__.'/../../.config/.procedures.php';

// 1. Receive $_GET
$headers = getallheaders();

// Validate

// 'adminId' => validInt($headers['Adminid']),
// 'groupId' => validInt($headers['Groupid'])


$filter = [
    // 'views' => [
    //     '$gte' => 100,
    // ],
];

if(isset(HEADERS['groupid'])) $filter['groupId'] = validInt($headers['groupid']);

$options = [
	'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
	'limit' => isset($_GET['limit']) ? $_GET['limit'] : 20,
	'sort' => ['_id' => -1],
    'projection' => [
        '_id'=>1, 'fname'=>1, 'lname'=>1, 'phone'=>1, 'email'=>1, 'active'=>1, 'roleId'=>1, 'role'=>1, 'created'=>1
    ],
];

print_j(mongoDateTime(mongoSelect(CUSER,$filter,$options)));