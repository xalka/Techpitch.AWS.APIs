<?php

$baseDir = dirname(__DIR__, 2);
require_once $baseDir.'/.config/.config.php';

$files = ['.funcs.php','.mongodb.php','.procedures.php','.mysql.php'];
foreach ($files as $file) require_once $baseDir.'/.core/'.$file;

if (ReqGet()) {

    print_r(HEADERS);

} elseif (ReqPost()) {
    
    $req = json_decode(file_get_contents('php://input'), true); 

    // 2. Validate
    print_r($req);

} else {
    ReqBad();
}