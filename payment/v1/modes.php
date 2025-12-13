<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mongodb.php';
// require __dir__.'/../../.core/.mysql.php'; 
// require __dir__.'/../../.core/.procedures.php';

if(!ReqGet()) ReqBad();

// $headers = array_change_key_case(getallheaders(), CASE_LOWER);

$filter = [];

if(isset($_GET['id'])) $filter['_id'] = validInt($_GET['id']);

$options = [
    'skip' => isset($_GET['start']) ? $_GET['start'] : 0,
    'limit' => isset($_GET['limit']) ? $_GET['limit'] : 20,
    'sort' => ['_id' => 1],
    'projection' => [
        '_id'=>1, 'mode'=>1, 'created'=>1
    ],	
];

print_j(mongoDateTime(mongoSelect(CMODE,$filter,$options)));