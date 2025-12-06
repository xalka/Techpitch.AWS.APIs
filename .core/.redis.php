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

    writeToFile(LOG_FILE_REDIS,": $key : ".$value);
    $redis->close();
    return $results;
}

function redisGetValue($key){
    $redis = redisConn();
    $results = $redis->get($key);
    $redis->close();
    return $results;
}

function redisConn(){
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