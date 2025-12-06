<?php


function queryDel($sql=null){
    $conn = dbConn($db=0);
    if($conn){
        writeToFile(LOG_FILE,$sql); // echo "\n";
        $query = $conn->prepare($sql);
        $query->execute();
        $conn = null;
        return $query->fetchAll();
    }
    writeToFile(LOG_FILE_FAILED,"MySQL server has gone away");
    die('Sorry, we have technical problems');
}

function query($sql = null) {
    $conn = dbConn($db = 0);
    if (!$conn) {
        writeToFile(LOG_FILE_FAILED, "Database connection failed");
        die('Sorry, we have technical problems');
    }

    try {
        writeToFile(LOG_FILE, $sql);
        $query = $conn->prepare($sql);
        $success = $query->execute();
        
        // Check if the query is a SELECT statement
        if (stripos(trim($sql), 'SELECT') === 0) {
            $result = $query->fetchAll();
            $conn = null; // Close connection
            return $result ?: []; // Return fetched data or an empty array
        }

        // For INSERT, UPDATE, DELETE, return success status (true/false)
        $conn = null; // Close connection
        return $success;

    } catch (PDOException $e) {
        writeToFile(LOG_FILE_FAILED, "Query Failed: " . $e->getMessage());
        return false; // Indicate failure
    }
}


function PROC001($proc){
    writeToFile(LOG_FILE,$proc);
    try {
        $conn = dbConn();
        if(!$conn){
            writeToFile(LOG_FILE,"MySQL server has gone away");
            die('Sorry, we have technical problems');
        }
        $stmt = $conn->query("CALL $proc");
        // return $stmt->fetchAll();
        do {
            $rows = $stmt->fetchAll();
            $return[] = $rows;
        } while ($stmt->nextRowset());
        // return $return;
        $conn=null;
        unset($conn);
        #if(sizeof($return)==1) return $return[0];
        #else return $return; //[0];//[0];
        // if(is_array($return[0])) return $return[0][0];
        if(!is_array($return[0])) return $return[0];
        else return $return;
    } catch(PDOException $e){
        die("Failed to call procedure: " . $e->getMessage());
    }
}

function PROC002($proc) {
    // Log the procedure call for debugging
    writeToFile(LOG_FILE, $proc);

    // Declare the return variable
    $return = [];

    // Database connection handling
    try {
        // Establish the database connection
        $conn = dbConn();
        
        if (!$conn) {
            writeToFile(LOG_FILE, "MySQL server has gone away");
            die('Sorry, we have technical problems');
        }

        // Execute the stored procedure
        $stmt = $conn->query("CALL $proc");

        // Loop through any multiple result sets from the procedure call
        do {
            // Fetch all results from the current result set
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Using FETCH_ASSOC for cleaner result sets
            if ($rows) {
                $return[] = $rows;  // Add the rows to the return array
            }
        } while ($stmt->nextRowset());  // Check for the next result set

    } catch (PDOException $e) {
        // Log and handle the exception
        writeToFile(LOG_FILE, "Error calling procedure: " . $e->getMessage());
        die("Failed to call procedure: " . $e->getMessage());
    } finally {
        // Close the connection properly
        if (isset($conn)) {
            $conn = null;  // Close the database connection
            unset($conn);  // Unset the connection variable
        }
    }
    return simplifyArray($return);
    
    // If only one result set is returned, return it directly
    if(count($return)===1){
        if(count($return[0])===1){
            if(count($return[0][0])===1) return $return[0][0];
        }
        return $return[0];
    }

    // Return the full array of result sets if more than one result set is returned
    return $return;
}

function PROC($proc) {
    // Log the procedure call for debugging
    writeToFile(LOG_FILE, "Proc: $proc");

    // Initialize the return variable
    $return = [];

    try {
        // Establish the database connection
        $conn = dbConn();
        if (!$conn) {
            throw new Exception("MySQL server is unavailable.");
        }

        // Prepare and execute the stored procedure
        $stmt = $conn->prepare("CALL $proc");
        $stmt->execute();

        // Fetch all result sets from the procedure
        do {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $return[] = $rows;
            }
        } while ($stmt->nextRowset());

        // Close the statement
        $stmt->closeCursor();

    } catch (PDOException $e) {
        // Log and return error message instead of terminating the script
        writeToFile(LOG_FILE, "Database error: " . $e->getMessage());
        return ["error" => "Database error occurred. Please try again later."];
    } catch (Exception $e) {
        writeToFile(LOG_FILE, "General error: " . $e->getMessage());
        return ["error" => $e->getMessage()];
    } finally {
        // Close the database connection
        $conn = null;
    }
    return $return;
    // Simplify and return the result
    // return simplifyArray($return);
}

function dbConn(){
    $db = DB;
    $host = DB_HOST;
    $user = DB_USER;
    $pass = DB_PASS;
    $port = DB_PORT;
    $charset = 'utf8';
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_COLUMN,
            PDO::ATTR_EMULATE_PREPARES   => false, // changed from false
            PDO::ATTR_CASE               => PDO::CASE_NATURAL,
            PDO::ATTR_ORACLE_NULLS       => PDO::NULL_EMPTY_STRING,
            PDO::ATTR_PERSISTENT         => true
        ];
        return new PDO($dsn,$user,$pass,$opt);
    } catch(PDOException $e){
        die("Failed to connect to MySQL: " . $e->getMessage());
    }
}

function simplifyArray001($return) {
    while(is_array($return) && count($return)===1){
        $return = array_values($return)[0]; // Extract the single element
    }
    return $return;
}

function simplifyArray($data) {
    if (count($data) === 1) {
        if (count($data[0]) === 1) {
            if (count($data[0][0]) === 1) {
                return array_values($data[0][0])[0]; // Return single value
            }
        }
        return $data[0]; // Return first result set if there's only one
    }
    return $data; // Return full dataset if multiple result sets exist
}