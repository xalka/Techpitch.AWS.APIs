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

if(isset($headers['Groupid'])){
    $groupId = validInt($headers['Groupid']);
    $filter['groups'] = ['$in' => [ $groupId ]];
}

if (isset($_GET['startdate']) && !empty($_GET['startdate'])) {
    $filter['created']['$gte'] = mongodate($_GET['startdate']);
}

if (isset($_GET['enddate']) && !empty($_GET['enddate'])) {
    $filter['created']['$lte'] = mongodate($_GET['enddate']);
}

if(isset($_GET['id']) & !empty($_GET['id'])) $filter['_id'] = validInt($_GET['id']);

if(isset($_GET['title']) & !empty($_GET['title'])){
    $title = validString($_GET['title']);

    if(validEmail($title)) $filter['email'] = validEmail($title);
    elseif(validPhone($title)) $filter['phone'] = validPhone($title);
    else {
        $filter['$or'] = [
            ['fname' => ['$regex' => $title, '$options' => 'i']],
            ['lname' => ['$regex' => $title, '$options' => 'i']],
            ['cname' => ['$regex' => $title, '$options' => 'i']]
        ];
    }
}

if(isset($_GET['roleId']) && isset($groupId)){
    $filter['roles'] = [
        '$elemMatch' => [
            'roleId' => validInt($_GET['roleId']),
            'groupId' => $groupId
        ]
    ];
}

if(isset($groupId)){
    $roles = [
        '$filter' => [
            'input' => '$roles',
            'as' => 'role',
            'cond' => [
                '$eq' => ['$$role.groupId', $groupId]
            ]
        ]
    ];
} else $roles = null;

$options = [
	'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
	'limit' => isset($_GET['limit']) ? $_GET['limit'] : LIMIT,
	'sort' => ['_id' => -1],
    'projection' => [
        '_id'=>1, 'typeId'=>1, 'cname'=>1, 'fname'=>1, 'lname'=>1, 'phone'=>1, 'email'=>1, 'img'=>1, 'address'=>1, 'roles'=>$roles, 'active'=>1, 'verified'=>1, 'created'=>1
    ],
];

print_j(formatMongoData(mongoSelect(CCUSTOMER,$filter,$options)));