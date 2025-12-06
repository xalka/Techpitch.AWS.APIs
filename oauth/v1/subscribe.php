<?php

require __dir__.'/.config.php';
// require __dir__.'/.func.php';
require __dir__.'/../../.proc/.proc.php';
require __dir__.'/../../.proc/.db.php';

try {   

    $redis = redisConn(); 
    $redis->setOption(Redis:: OPT_READ_TIMEOUT, -1);       

    $redis->subscribe([REDIS_TOKEN_CHANNEL], function($instance, $channelName, $data) { 

        $data = dejson($data); 

        // if(isset($data['phone'])) $data['action'] = 1;
        // elseif(isset($data['username'])) $data['action'] = 3;
        // else exit('Bad request');

        $data['action'] = 3;
        $data['starttime'] = $data['expiry'];

        $return = proc(Business($data))[0];

        print_r(Business($data)); echo "\n";

        if($return['updated']==1){
            echo "The Redis data was sent to the MySQL database and updated successfully.\n";
        } else {
            echo "The Redis data was sent to the MySQL database was unsuccessfully updated.\n";
        }     

    });

} catch (Exception $e) {
    echo $e->getMessage();
}