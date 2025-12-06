<?php

// https://www.php.net/manual/en/class.mongodb-driver-writeresult.php

function mongoConnectI(){
	return new \MongoDB\Driver\Manager("mongodb://".DB_MONGO_USER.":".rawurlencode(DB_MONGO_PASS)."@".DB_MONGO_HOST.":".DB_MONGO_PORT);
}

function mongoConnect(): ?\MongoDB\Driver\Manager {
    try {
        $uri = sprintf(
            "mongodb://%s:%s@%s:%d/?authSource=%s",
            DB_MONGO_USER,
            rawurlencode(DB_MONGO_PASS),
            DB_MONGO_HOST,
            DB_MONGO_PORT,
            DB_MONGO_AUTH_DB
        );
        return new \MongoDB\Driver\Manager($uri);
    } catch (\MongoDB\Driver\Exception\Exception $e) {
        echo "Connection failed: ", $e->getMessage(), "\n";
        return null;
    }
}

function mongoSelect($collection,$filter=[],$options=[]){
    $manager = mongoConnect();
    if (!$manager) return;
	
	$query = new MongoDB\Driver\Query($filter, $options);
	return $manager->executeQuery(DB_MONGO.'.'.$collection, $query)->toArray();
}

function mongoInsert($collection,$value=null){
    $manager = mongoConnect();
    if (!$manager) return;
	
	$bulkWrite = new MongoDB\Driver\BulkWrite;
	$bulkWrite->insert($value);
	try {
	    $return = $manager->executeBulkWrite(DB_MONGO.'.'.$collection, $bulkWrite);	
		return $return->getInsertedCount();
	} catch(MongoDB\Driver\Exception\BulkWriteException $e){
	    throw new Exception('Exception message : '.$e->getMessage());
	}
}

function mongoUpdate1($collection,$filter=[], $update=[], $options=['multi'=>true,'upsert'=>true]){
    $manager = mongoConnect();
    if (!$manager) return;
	
	$update = ['$set' => $update];
	$bulkWrite = new MongoDB\Driver\BulkWrite;
	$bulkWrite->update($filter, $update, $options);
	try {
		$return = $manager->executeBulkWrite(DB_MONGO.'.'.$collection, $bulkWrite); 
		return $return->getModifiedCount();
	} catch(MongoDB\Driver\Exception\BulkWriteException $e){
	    throw new Exception('Exception message : '.$e->getMessage());
	}
}

function mongoUpdate($collection, $filter = [], $updateFields = [], $options = ['multi' => true, 'upsert' => true]) {
    if (empty($updateFields)) {
        throw new Exception('No fields provided for update.');
    }
    
    $manager = mongoConnect();
    if (!$manager) return;
	
    $update = ['$set' => $updateFields];
    $bulkWrite = new MongoDB\Driver\BulkWrite;
    $bulkWrite->update($filter, $update, $options);
    
    try {
        $result = $manager->executeBulkWrite(DB_MONGO . '.' . $collection, $bulkWrite);
        return $result->getModifiedCount();
    } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
        throw new Exception('MongoDB Bulk Write Exception: ' . $e->getMessage());
    } catch (MongoDB\Driver\Exception\Exception $e) {
        throw new Exception('MongoDB Exception: ' . $e->getMessage());
    }
}

function mongoInsertOrUpdate($collection,$filter=[], $update=[], $options=['multi'=>true,'upsert'=>true]){
    $manager = mongoConnect();
    if (!$manager) return;
	
	$update = ['$setOnInsert' => $update, '$set' => $update];
	$bulkWrite = new MongoDB\Driver\BulkWrite;
	$bulkWrite->update($filter, $update, $options);
	try {
	    $return = $manager->executeBulkWrite(DB_MONGO.'.'.$collection, $bulkWrite);	
		if($return->getModifiedCount()){
			return $return->getModifiedCount();
		} elseif($return->getUpsertedCount()){
			return $return->getUpsertedCount();
		}
	} catch(MongoDB\Driver\Exception\BulkWriteException $e){
	    throw new Exception('Exception message : '.$e->getMessage());
	}
}

function mongoDelete001($collection,$filter=[], $options=[]){
    $manager = mongoConnect();
    if (!$manager) return;
	
	$bulkWrite = new MongoDB\Driver\BulkWrite;
	$bulkWrite->delete($filter, $options);
	try {
		$result = $manager->executeBulkWrite(DB_MONGO.'.'.$collection, $bulkWrite);	
		return $return->getDeletedCount();
	} catch(MongoDB\Driver\Exception\BulkWriteException $e){
	    throw new Exception('Exception message : '.$e->getMessage());
	}
}

function mongoDelete($collection, $filter = [], $options = []) {
    $manager = mongoConnect();
    if (!$manager) return;
	
    $bulkWrite = new MongoDB\Driver\BulkWrite;
    $bulkWrite->delete($filter, $options);
    try {
        $result = $manager->executeBulkWrite(DB_MONGO . '.' . $collection, $bulkWrite);
        return $result->getDeletedCount();
    } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
        throw new Exception('Exception message: ' . $e->getMessage());
    }
}


function mongoAggregate001($command=null){
    $manager = mongoConnect();
    if (!$manager) return;
	
	// $query = new MongoDB\Driver\Query($filter, $options);
	// return $manager->executeQuery(DB_MONGO.'.'.$collection, $query)->toArray();
	$command = new MongoDB\Driver\Command($command);
	return $manager->executeCommand(DB_MONGO,$command)->toArray();
}

function mongoAggregate($collection,$pipeline) {
    $manager = mongoConnect(); // Your custom connection helper
    if (!$manager) return [];

    $command = new MongoDB\Driver\Command([
        'aggregate' => $collection,
        'pipeline' => $pipeline,
        'cursor' => new stdClass()
    ]);

    try {
        $cursor = $manager->executeCommand(DB_MONGO, $command);
        return $cursor->toArray();
    } catch (MongoDB\Driver\Exception\Exception $e) {
        echo "Aggregation error: ", $e->getMessage(), "\n";
        return [];
    }
}

function mongodate($value){
    return new MongoDB\BSON\UTCDateTime(strtotime($value)*1000);
}

function localdate($value,$format='Y-m-d H:i:s'){
	if(is_string($value)) return $value;
    return $value->toDateTime()->setTimeZone(new \DateTimeZone(date_default_timezone_get()))->format($format);
    // return $value->toDateTime()->setTimeZone(new \DateTimeZone(date_default_timezone_get())).format('Y-m-d H:i:s');
}

function mongoDateTime(array $data): array {
    return array_map(function($obj) {
        if(isset($obj->created)){
            $obj->created = localdate($obj->created);
            $timestamp = strtotime($obj->created);
            $obj->date = date('Y-M-d', $timestamp);
            $obj->time = date('H:i', $timestamp);            
        }
        if(isset($obj->delivered)) $obj->delivered = localdate($obj->delivered);
        if(isset($obj->scheduled)) $obj->scheduled = localdate($obj->scheduled);
        return $obj;
    }, $data);
}

function formatMongoData(array $data): array {
    return array_map(function($obj) {
        if(isset($obj->created)){
            $obj->created = localdate($obj->created);
            $timestamp = strtotime($obj->created);
            $obj->date = date('Y-M-d', $timestamp);
            $obj->time = date('H:i', $timestamp);            
        }
        if(isset($obj->delivered)) $obj->delivered = localdate($obj->delivered);
        if(isset($obj->scheduled)) $obj->scheduled = localdate($obj->scheduled);
        if(isset($obj->active)) $obj->active = $obj->active ? 'active' : 'in-active';
        if(isset($obj->verified)) $obj->verified = $obj->verified ? 'verified' : 'unverified';
        return $obj;
    }, $data);
}

function autoincreament($collection){
	$options = ['limit' => 1,'sort' => ['_id' => -1], 'projection' => ['_id' => 1] ];
	$id = mongoSelect([],$options,$collection);
	return empty($id) ? 1 : $id[0]->_id+1;
}