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

// 1. Receive $_GET
$headers = getallheaders();

// $record = [ 'name' => 'techxal', 'email' => 'info@techxal.co.ke', 'phone' => '254715003414' ];

$filter = [
    // 'views' => [
    //     '$gte' => 100,
    // ],
];

// $filter = [
//     'groupId' => 1,
//     'blocked' => false,
//     'sent' => ['$gte' => $startDate, '$lte' => $endDate]
// ];

if(isset($headers['Groupid'])) $filter['groupId'] = validInt($headers['Groupid']);

$options = [
	'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
	'limit' => isset($_GET['limit']) ? $_GET['limit'] : LIMIT,
	'sort' => ['_id' => -1],
    /* Only return the following fields in the matching documents */
    'projection' => [
        '_id'=>1, 'messageId'=>1, 'groupId'=>1, 'phone'=>1, 'statusId'=>1, 'status'=>1, 'alphanumericId'=>1, 'alphanumeric'=>1, 'blocked'=>1, 'created'=>1, 'delivered'=>1
    ],
];

print_j(mongoDateTime(mongoSelect(CDLR,$filter,$options)));