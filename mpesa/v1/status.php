<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

// GET request only
if(!ReqGet()) ReqBad();

// 1. Receive $_GET
print_r($_GET);

// 2. validate

// 3. read from mysql