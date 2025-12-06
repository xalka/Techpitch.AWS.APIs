<?php
print_r(['name'=>'kipkoech']);
define('DB_MONGO_NAME', 'tpsys');
define('DB_MONGO_HOST', '127.0.0.1');
define('DB_MONGO_PORT', 27017);
define('DB_MONGO_USER', 'devOps');
define('DB_MONGO_PASS', 'working.Dev2');
define('DB_MONGO_AUTH_DB', 'admin'); // The DB where the user is defined

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

function fetchUsers() {
    $manager = mongoConnect();
    if (!$manager) return;

    try {
        $query = new \MongoDB\Driver\Query([]);
        $cursor = $manager->executeQuery(DB_MONGO_NAME . '.user', $query);

        foreach ($cursor as $document) {
            print_r($document);
        }
    } catch (\MongoDB\Driver\Exception\Exception $e) {
        echo "Query failed: ", $e->getMessage(), "\n";
    }
}

fetchUsers();
