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

$filter = [];

if(isset($_GET['roleId']) && !empty($_GET['roleId'])){
    $filter['roleId'] = validInt($_GET['roleId']);
    $options = [
        'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
        'limit' => isset($_GET['limit']) ? $_GET['limit'] : 20,
        'sort' => ['_id' => 1],
        'projection' => [
            '_id'=>1, 'title'=>1, 'permissions'=>1, 'created'=>1
        ],	
    ];

} else {
    $options = [
        'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
        'limit' => isset($_GET['limit']) ? $_GET['limit'] : 20,
        'sort' => ['_id' => 1],
        'projection' => [
            '_id'=>1, 'role'=>1, 'created'=>1
        ],	
    ];    
}

print_j(mongoDateTime(mongoSelect(CROLE,$filter,$options)));