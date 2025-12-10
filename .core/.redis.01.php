<?php

function redisPub($channel=null,$data=null){
    if(is_array($data)) $data = json_encode($data);
    $redis = redisConn();
    $results = $redis->publish($channel,$data);
    $redis->close();
    return $results;
}

function redisKeyExists($key){
    $redis = redisConn();
    $results = $redis->exists($key);
    $redis->close();
    return $results;
}

function redisSetValue($key,$value,$expire=null){
    // value should be a json encoded
    $redis = redisConn();
    $results = $redis->set($key,$value);
    if($expire) $redis->expire($key,$expire);

    // writeToFile(LOG_FILE_REDIS,": $key : ".$value);
    $redis->close();
    return $results;
}

function redisGetValue01($key){
    $redis = redisConn();
    $results = $redis->get($key);
    $redis->close();
    return $results;
}

function redisGetValue(string $key) {
    try {
        $redis = redisConn();

        if (!$redis) {
            writeToFile(LOG_FILE_REDIS, "redisGetValue: Failed to obtain Redis connection");
            return null;
        }

        $value = $redis->get($key);

        // Close connection cleanly
        if ($redis->isConnected()) {
            $redis->close();
        }

        return $value !== false ? $value : null;

    } catch (Exception $e) {
        writeToFile(LOG_FILE_REDIS, "redisGetValue Error: " . $e->getMessage());
        return null;
    }
}


function redisConn(): Redis {
    try {
        $redis = new \Redis();
        
        // Use pconnect() for persistent connection across requests (faster for web apps)
        // Use connect() if the connection should close when the script finishes
        $redis->connect(REDIS_HOST, REDIS_PORT); 
        
        // Authenticate if a password is provided
        if (defined('REDIS_PASS') && REDIS_PASS !== '') {
            if (!$redis->auth(REDIS_PASS)) {
                // If auth fails, throw an exception immediately
                throw new Exception("Redis authentication failed.");
            }
        }

        // Select the correct database index if defined
        if (defined('REDIS_DB_INDEX')) {
            $redis->select(REDIS_DB_INDEX);
        }

        return $redis;

    } catch (RedisException $e) {
        // Log the specific Redis error message
        // Make sure writeToFile function is defined elsewhere
        writeToFile(LOG_FILE_REDIS, ": Redis connection failed: " . $e->getMessage());
        
        // Re-throw the exception so the calling code knows the connection failed
        throw new Exception("Redis connection failed: " . $e->getMessage());

    } catch (Exception $e) {
         // Handle generic exceptions (e.g., if Redis class doesn't exist)
         writeToFile(LOG_FILE_REDIS, ": An unexpected error occurred: " . $e->getMessage());
         throw new Exception("Redis connection failed due to an unexpected error: " . $e->getMessage());
    }
}


function redisConn_del(){
    try {
        // $this->redis = new Redis();
        // $this->redis->connect('127.0.0.1', 6379);
        $redis = new \Redis;
        $redis->connect(REDIS_HOST,REDIS_PORT);  
        return $redis;      
    } catch (Exception $e) {
        writeToFile(LOG_FILE_REDIS,": Redis connection failed: " . $e->getMessage());
        throw new Exception("Redis connection failed: " . $e->getMessage());
    }
    /*
    $redis = new \Redis;
    $redis->connect(REDIS_HOST,REDIS_PORT);
    // if($redis->rawCommand("auth",REDIS_USER,REDIS_PASS)){
    // if($redis->auth(REDIS_PASS)){
    if(1==1){
        return $redis;
    } 
    writeToFile(LOG_FILE_REDIS,': Error on Redis Connection');
    */
}