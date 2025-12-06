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


$filter = [];

if (isset($_GET['id']) && !empty($_GET['id'])) $filter['_id'] = validInt($_GET['id']);

if(isset($_GET['title']) & !empty($_GET['title'])){
    $title = validString($_GET['title']);

    if(validPhone($title)) $filter['phone'] = validPhone($title);
    elseif(validEmail($title)) $filter['email'] = validEmail($title);
    else {
        $regex = [ '$regex' => validString($title), '$options' => 'i' ];
        $filter['$or'] = [
            [ 'cname' => $regex ],
            [ 'fname' => $regex ],
            [ 'lname' => $regex ]
        ];
    }
}

$options = [
	'skip' => isset($_GET['start']) ? $_GET['start'] : START,
	'limit' => isset($_GET['limit']) ? $_GET['limit'] : LIMIT,
	'sort' => ['_id' => -1],
    'projection' => [
        '_id'=>1, 'adminId'=>1, 'cname'=>1, 'fname'=>1, 'lname'=>1, 'phone'=>1, 'email'=>1, 'active'=>1, 'units'=>1, 'verified'=>1, 'type'=>1, 'created'=>1
    ],
];

print_j(mongoSelect(CACCOUNT,$filter,$options));
// print_j(mongoDateTime(mongoSelect(CACCOUNT,$filter,$options)));