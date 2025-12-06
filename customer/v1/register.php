<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

// POST request only
if(!ReqPost()) ReqBad();

// 1. Receive json
$req = json_decode(file_get_contents('php://input'),1);

// corporate
if( isset($_GET['type']) && $_GET['type']=='corporate') require __dir__.'/register-corporate.php';

// individual
else require __dir__.'/register-individual.php';