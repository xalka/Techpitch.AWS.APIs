<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
// require __dir__.'/../../.config/.redis.php'; 
// require __dir__.'/../../.config/Redis/Connection.php'; 
// require __dir__.'/../../.config/Redis/Queue.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

$roles = [
    [
        '_id' => 1,
        'title' => 'admin',
        'permissions' => [],
        'created' => mongodate('NOW')
    ],
    [
        '_id' => 2,
        'title' => 'user',
        'permissions' => [],
        'created' => mongodate('NOW')
    ],
    [
        '_id' => 3,
        'title' => 'api',
        'permissions' => [],
        'created' => mongodate('NOW')
    ]
];

foreach ($roles as $key => $value) {
    $return2 = mongoInsert(CROLE,$value);
    print_r($return2);
}


// 1. Bubpavacode
// 2. Ezestatecycle
// 3. Esticure
