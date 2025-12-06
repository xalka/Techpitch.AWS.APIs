<?php

// development
/*
define('DB_USER','siokaDev');
define('DB_PASS','NOV.2014.TEN.sioka');

define('DB_MONGO_USER', 'devOps');
define('DB_MONGO_PASS', 'working.Dev2');
*/

// production
define('DB_USER','sioka');
define('DB_PASS','taoism.2O22');

define('DB_MONGO_USER', 'sioka');
define('DB_MONGO_PASS', 'lit#!dAk0l');

define('DB', 'xalbusiness');
define('DB_MONGO', 'xalbusiness');
define('DB_COLLECTION','businesses');
define('REDIS_TOKEN_CHANNEL', 'TokenRedis2DBChannel');

// define('LOG_FILE',__DIR__.'/logs/'.date('Y-m-d').'.log');
define('LOG_FILE','/tmp/oauth-'.date('Y-m-d').'.log');
define('LOG_FILE_REDIS',__DIR__.'/logs/'.date('Y-m-d').'_redis.log');

define('TOKEN_EXPIRY', 1800);

require __DIR__.'/../../.config/.config.php';
require __DIR__.'/../../.config/.func.php';