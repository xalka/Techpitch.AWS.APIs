<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';

$headers = getallheaders();

$pipeline = [
    [
        '$group' => [
            '_id' => [
                'roleId' => '$roleId',
                'role' => '$role'
            ]
        ]
    ]
];

print_j(mongoAggregate(CROLE,$pipeline));