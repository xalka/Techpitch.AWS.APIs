<?php

function img2base64($img) {
    $image = file_get_contents($img);
    $base64 = base64_encode($image);
    return 'data:image/jpeg;base64,' . $base64;    
}

function bulkSDP($payload){
    $token = json_decode(redisGetValue(SDP_TOKEN),1);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest',
        'X-Authorization: Bearer '.$token
    ];
    $request = [
        "timeStamp" => SDP_TIMESTAMP,
        "dataSet" => [
            [
                "userName" => SDP_USERNAME2,
                "channel" => "SMS",
                "packageId" => SDP_PACKAGE_ID, // in case you have multiple package id in your account, don’t specify the package id, remove it completely
                "oa" => SDP_ALPHANUMERIC, // $payload['alphanumeric'],
                "cpPassword" => md5(SDP_ID.SDP_PASSWORD2.SDP_TIMESTAMP),
                "msisdn" => implode(',',array_column($payload['contacts'],'phone')),
                "message" => $payload['message'],
                "uniqueId" => $payload['messageId'],
                "actionResponseURL" => SDP_CALLBACK."sdp/v1/dlrbulk"
            ]
        ]
    ];
    writeToFile(LOG_SDP,json_encode($request,JSON_PRETTY_PRINT));
    // Production remove
    $log = [
        'message' => $request['dataSet'][0]['message'],
        'msisdn' => $request['dataSet'][0]['msisdn']
    ];
    writeToFile(LOG_SDP,json_encode($log,JSON_PRETTY_PRINT));

    // return json_encode([
    //     "keyword" => "Bulk",
    //     "status" => "SUCCESS",
    //     "statusCode" => "SC0000"
    // ]);
    $return = callAPI("POST", SDP1.'api/public/CMS/bulksms', $headers, $request);   
    writeToFile(LOG_SDP,$return);
    return $return;
}

function saveMessageRecipientsI($payload){
    $messageId = validInt($payload['messageId']);
    $chunks = array_chunk($payload['contacts'],MESSAGE_CHUNKS);
    $failed = 0;

    // loop recipients saving them
    foreach ($chunks as $chunk) {
        $values = [];
        foreach ($chunk as $contact) { 
            // Properly escape the values and format the SQL query
            $phone = $contact['phone'];
            $fname = addslashes($contact['fname']); // Escape single quotes
            $lname = addslashes($contact['lname']); // Escape single quotes
    
            $values[] = "($messageId, '$phone', '$fname', '$lname')";
        }
        $sql = "INSERT INTO messagesRecipients(messageId, phone, fname, lname) VALUES " . implode(',', $values);
        $result = query($sql);

        if(!$result) {
            $failed += 1;
            writeToFile(LOG_FILE_FAILED,$sql);
            // break; // Stop execution on first failure
        }

        if($failed==0){
            return [
                'status' => 200,
                'message' => 'Recipients saved successfully',
                'messageId' => $messageId
            ];
        } else {
            return [
                'status' => 500,
                'message' => 'Failed to save recipients',
                'messageId' => $messageId,
                'queries' => $failed
            ];
        }
    }   
}

function saveMessageRecipients($payload) {
    // --- 1. Input Validation and Initialization ---

    // Validate and sanitize messageId.
    // Assuming 'validInt' is a function that securely validates and returns an integer,
    // or null/false if invalid. If not defined, you might need to implement it,
    // e.g., filter_var($payload['messageId'], FILTER_VALIDATE_INT).
    $messageId = validInt($payload['messageId'] ?? null); // Use null coalescing to prevent undefined index notice
    if ($messageId === null || $messageId <= 0) {
        error_log("saveMessageRecipients: Invalid or missing messageId in payload. Received: " . var_export($payload['messageId'] ?? 'N/A', true));
        return [
            'status' => 400, // Bad Request
            'message' => 'Invalid or missing message ID. Must be a positive integer.',
            'messageId' => null // Return null as messageId was invalid
        ];
    }

    // Validate 'contacts' data. It must be an array.
    if (!isset($payload['contacts']) || !is_array($payload['contacts'])) {
        error_log("saveMessageRecipients: 'contacts' array is missing or invalid for messageId: " . $messageId);
        return [
            'status' => 400, // Bad Request
            'message' => "'contacts' data is missing or invalid. Must be an array.",
            'messageId' => $messageId
        ];
    }

    // Define chunk size.
    // It's good practice to ensure MESSAGE_CHUNKS is defined. If not, provide a sensible default.
    // Example: define('MESSAGE_CHUNKS', 1000);
    // $chunkSize = defined('MESSAGE_CHUNKS') ? MESSAGE_CHUNKS : 20;
    $chunks = array_chunk($payload['contacts'], MESSAGE_CHUNKS);

    $failedQueriesCount = 0; // Counter for chunks that failed to insert

    // --- 2. Loop through recipient chunks and save them ---
    foreach ($chunks as $index => $chunk) {
        $values = [];
        foreach ($chunk as $contact) {
            // Basic validation for individual contact fields.
            // Using empty string as default for missing fields to avoid 'undefined index' notices.
            $phone = $contact['phone'] ?? '';
            $fname = $contact['fname'] ?? '';
            $lname = $contact['lname'] ?? '';

            // --- IMPORTANT SECURITY NOTE ---
            // Using addslashes() is generally NOT recommended for preventing SQL injection.
            // It can be insufficient and lead to vulnerabilities, especially with different
            // character encodings or complex query structures.
            // The **BEST PRACTICE** is to use **Prepared Statements** with PDO or MySQLi.
            // Example (conceptual, as 'query()' is a custom function):
            // If `query()` can handle prepared statements, you would pass an array of parameters:
            // $sql = "INSERT INTO messagesRecipients(messageId, phone, fname, lname) VALUES (?, ?, ?, ?)";
            // $params = [$messageId, $phone, $fname, $lname];
            // $result = query($sql, $params);
            //
            // If your `query()` function only accepts raw SQL strings, then `addslashes`
            // is a minimal safeguard, but it's crucial to understand its limitations.
            // You might consider a more robust escaping function provided by your database driver
            // (e.g., mysqli_real_escape_string() if using MySQLi).

            $escapedPhone = addslashes($phone);
            $escapedFname = addslashes($fname);
            $escapedLname = addslashes($lname);

            $values[] = "($messageId, '$escapedPhone', '$escapedFname', '$escapedLname')";

            // Save into mongoDB
            // Tobe implemented
            /*
            $mongoData = [
                '_id' => $messageId,
                'messageId' => $messageId,
                'groupId': 1,
                'phone': Long('254719824139'),
                'fname': 'Techxal',
                'lname': 'Techxal',
                'statusId': 8,
                'status': 'delivered',
                'alphanumericId': 1,
                'alphanumeric': 'Techxal',
                'blocked': false,
                'sent': false,
                'successMessage'
                'errorMessage'
                'delivered': ISODate('2025-02-25T12:46:33.000Z'),
                'created': ISODate('2025-03-17T10:37:14.000Z'),
                'updated': ISODate('2025-03-17T10:37:14.000Z')
            ];
            print_r($mongoData);     
            */       

        }

        // Only attempt to execute an INSERT query if there are actual values to insert.
        if (!empty($values)) {
            $sql = "INSERT INTO messagesRecipients(messageId, phone, fname, lname) VALUES " . implode(',', $values);
            // Assuming query() executes the SQL and returns true on success, false on failure.
            $result = query($sql);

            if(!$result){
                $failedQueriesCount++;
                // Log the failed query.
                // Assuming LOG_FILE_FAILED is a defined constant (e.g., '/var/log/app/failed_sqls.log').
                // The writeToFile function (from previous correction) should handle permissions.
                writeToFile(LOG_FILE_FAILED, [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'messageId' => $messageId,
                    'chunk_index' => $index,
                    'error' => 'Database insert failed for chunk',
                    'sql' => $sql // Log the full SQL for debugging
                ]);
                error_log("saveMessageRecipients: Failed to save chunk " . $index . " for messageId " . $messageId . ".");
                // It's generally better to log and continue processing other chunks
                // unless a single failure should halt the entire operation (e.g., transactional).
                // The original code had a commented 'break;', which would stop on first failure.
                // I'm keeping it uncommented to process all chunks and report total failures.

            }

        }
    }

    // --- 3. Final Return Logic (After all chunks have been processed) ---
    // The return statement must be outside the loop to ensure all chunks are processed.
    if ($failedQueriesCount === 0) {
        return [
            'status' => 200,
            'message' => 'All recipients saved successfully.',
            'messageId' => $messageId
        ];
    } else {
        return [
            'status' => 500, // Internal Server Error, as some operations failed
            'message' => 'Failed to save ' . $failedQueriesCount . ' out of ' . count($chunks) . ' recipient chunks.',
            'messageId' => $messageId,
            'failed_chunks_count' => $failedQueriesCount // Clearer name for the count of failed chunks
        ];
    }
}

function SaveMessage($payload){
    // 1. save into database
    $dbdata = [
        'action' => 1,
        'title' => $payload['title'],
        'message' => $payload['message'],
        'contactGroupId' => $payload['contactGroupId'],
        'contacts' => $payload['contacts'],
        'recipients' => $payload['recipients'],
        'alphanumeric' => $payload['alphanumeric'],
        'alphanumericId' => $payload['alphanumericId'],
        'transactionId' => isset($payload['transactionId']) ? $payload['transactionId'] : null,
        'customerId' => $payload['customerId'],
        'pgroupId' => $payload['pgroupId'],
        'units' => $payload['units'],
        'statusId' => $payload['statusId'],
        'typeId' => $payload['typeId'],
        'mode' => $payload['mode'],        
        'sent' => 0
    ];

    if(isset($payload['scheduled']) && !empty($payload['scheduled'])) $dbdata['scheduled'] = date('Y-m-d H:i:s',strtotime($payload['scheduled']));
    else unset($payload['scheduled']);  
    
    $return = PROC(Message($dbdata))[0][0];
    
    // 2. save into mongodb
    if(isset($return['created']) && $return['created']>0){
        $dbdata['_id'] = $return['messageId'];
        $dbdata['contacts'] = $payload['contacts']; // should be moved to messageRecipients collection
        $dbdata['status'] = $payload['status'];
        $dbdata['sent'] = 0;
        $dbdata['type'] = $dbdata['typeId']==1 ? 'transaction' : 'bulk';
        $dbdata['mode'] = $dbdata['mode']==0 ? 'normal' : 'custom';
        $dbdata['created'] = mongodate('NOW');
        if(isset($dbdata['scheduled'])) $dbdata['scheduled'] = mongodate($dbdata['scheduled']);
        unset($dbdata['action']);
    
        foreach ($dbdata as $key => $value){
            if(is_numeric($value)) $dbdata[$key] = validInt($value);
        }
        if(mongoInsert(CMESSAGE,$dbdata)){
            $response = [ 
                '_id' => $dbdata['_id'],
                'created' => 1
            ];
        } else {
            $response = [
                'created' => 0,
                'message' => 'Unable to save into mongodb'
            ];
        }
    } else {
        $response = [
            'created' => 0
        ];
    }
    return $response;
}

function getUserFromPhone($phone=null){
    $url = OAUTH_ENDPOINT.'/user/v1/user?phone='.$phone;
    $header = array(
        'content-type: application/json',
        'authtoken: '.TOKEN,
        // 'userphone: no-cache'        
    );
    curlGet($url=null,$header=null);
}

function findIndexByName($items, $name) {
    foreach ($items as $index => $item) {
        if (isset($item['Name']) && $item['Name'] === $name) {
            return $index;
        }
    }
    return -1; // Return -1 if not found
}

function authorized(){
    // $location = $_SERVER['REQUEST_URI'];
    if(!isset($_SESSION[SESSION_KEY])) redirect('/user?action=logout');
    else {
        return 1;
        /*if(!isset($_SESSION[SESSION_KEY]['auth'])) $this->redirect('/user/logout'.$location);
        else { // return 1;
            $page = explode('/',$_SERVER['REQUEST_URI'])[1];
            $roles = explode(',',$this->loggedInUser()['roles']);
            array_push($roles,'user');
            // print_r($page);
            // print_r($roles);

            if(in_array('inpatient',$roles) && $page=='provider'){
                array_push($roles,'provider');
            }

            // die();
            if(!in_array($page,$roles) && !empty($page)){ // return 0;
                $this->ReqBad();
                // $this->redirect('errors/forbidden');
                // $this->redirect('/'.$roles[0]);
            } else return 1;
        }*/
    }
}

function authenticate($request=null){
    $reqheaders = getallheaders();
    if(!isset($reqheaders['Businessid'])) ReqForbidden();
    if(isset($reqheaders['X-Oauth'])){
        $url = DOMAIN.'/oauth/v1/authenticate';
        $headers = [
            'Content-Type: application/json',
            'Reference: '.$reqheaders['Reference'],
            'BusinessId: '.$reqheaders['Businessid'],
            'UserId: '.$reqheaders['Userid'],
            'Authtoken: '.$reqheaders['Authtoken']
        ];
        if(!deJson(callAPI('POST',$url,$headers,$request))['auth']) ReqForbidden();
    }
}

function callAPI($method=null, $url=null, $headers=null, $request=null){
    if(is_null($url)) die('Request parameters required');
    if(is_array($request)) $request = json_encode($request);   

    $curl = curl_init();

    switch ($method){
        case "POST":
            curl_setopt($curl, CURLOPT_POST, true);
            if($request) curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            break;

        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if($request) curl_setopt($curl, CURLOPT_POSTFIELDS, $request);                              
            break;

        case "PATCH":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
            if($request) curl_setopt($curl, CURLOPT_POSTFIELDS, $request);                              
            break;

        case "GET":
        default:
            if($request) $url = sprintf("%s?%s", $url, http_build_query(json_decode($request,1)));
    }

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 1
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 2    
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
    
    // OPTIONS:
    // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // curl_setopt($curl, CURLOPT_SSLCERT, '/etc/ssl/mycerts/server.crt');
    // curl_setopt($curl, CURLOPT_SSLKEY, '/etc/ssl/mycerts/server.key');
    // curl_setopt($curl, CURLOPT_CAINFO, '/etc/ssl/mycerts/server.crt');

    // EXECUTE:
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        return "Curl error: " . $error;
    }
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($status === 200) return $response;
    else return "Failed; Status: $status. Response: $response";
}

function decrypt($data) { return $data;
    $data = base64_decode($data);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data,0,$ivLength);
    $encryptedData = substr($data,$ivLength);
    return openssl_decrypt($encryptedData,'aes-256-cbc',PASSWORD_KEY,0,$iv);
}

function passEncrype($pass,$cost=10){
    $options = ['cost'=>$cost];
    return password_hash($pass,PASSWORD_BCRYPT,$options);
}

function unnumber_format($value=null){
    return filter_var(str_replace('.00','',$value),FILTER_SANITIZE_NUMBER_INT);
}

function datetime($value=null){
    return date('Y-m-d H:i:s', strtotime($value));
}

function strRand($length=6){
    // return strtoupper(substr(md5(rand(0,time())),$start,$length)).time();
    $length = max(4, $length); // Ensure at least 4 characters
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $otp = '';
    $maxIndex = strlen($characters)-1;
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[random_int(0, $maxIndex)];
    }
    return $otp;    
}

function intRand($length=6){
    $length = max(4, $length);
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= random_int(0, 9);
    }
    return validInt($otp);
} 

function validString(?string $value): string|false {
    if ($value !== null && strlen(trim($value)) > 0) {
        return filter_var(trim($value), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
    return false;
}

function validInt($value = null): int|false {
    if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
        return (int)$value;
    }
    return false;
}

function validPhone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        $phone = '254' . substr($phone, 1);
    } elseif (substr($phone, 0, 3) !== '254'){
        $phone = '254'.$phone;
    }

    // Validate the phone number against the Kenyan format
    $pattern = '/^254(?:[17]\d{8}|[2-9]\d{7})$/';
    if (preg_match($pattern, $phone)) {
        return (int)$phone; // Return as an integer
    } else {
        return false; // Invalid phone number
    }
}

function formatValue($value, $isString = false) {
    if (!isset($value) || $value === '') return 'NULL';
    return $isString ? "'" . addslashes($value) . "'" : $value;
}

function validEmail(?string $email): string|false {
    $sanitized = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
        return strtolower($sanitized);
    }
    return false;
}

function generateToken($prefix=null){
    // $prefix = $prefix ? $prefix : time();
    // return crypt($prefix.intRand(20).strRand(3,23)).time().crypt($prefix.intRand(20).strRand(3,23));
    return uniqid().strRand(30).intRand(time()).time().strRand(0,time()).intRand(time());
}

function print_j($value=null){
    // http_response_code((int)$code);
    header('Content-Type: application/json');
    print_r(json_encode($value));
}

function redirect($page=null){
    header("Location: ".$page);
    exit;
}

function enJson($value=null){
    return json_encode($value);
}

function deJson($value=null){
    // header('Content-Type: text/plain; charset=utf-8');
    return json_decode($value,true);
}

function ReqJson(){
	if($_SERVER['HTTP_CONTENT_TYPE'] == 'application/json') return 1;
	else return 0;
}

function ReqAjax(){
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])&&strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest') return 1;
	else return 0;
}

function ReqPost(){
    if($_SERVER['REQUEST_METHOD']=='POST') return 1;
    else return 0;
}

function ReqGet(){
    if($_SERVER['REQUEST_METHOD']=='GET') return 1;
    else return 0;
}

function ReqDelete(){
    if($_SERVER['REQUEST_METHOD']=='DELETE') return 1;
    else return 0;
}	

function ReqPut(){
    if($_SERVER['REQUEST_METHOD']=='PUT') return 1;
    else return 0;
}

function ReqBad(){
    header('HTTP/1.0 400 Bad Request');
    http_response_code(400);
    exit;
}

function ReqNotFound(){
	header("HTTP/1.0 404 Not Found");
    http_response_code(404);
	exit;
}

function ReqForbidden(){
    header("HTTP/1.0 403 Forbidden");
    http_response_code(403);
    exit;
}

function ReqInternalServerError(){
    header("HTTP/1.0 500 Internal Server Error");
    http_response_code(500);
    exit;
}

function ReqMethodNotAllowed(){
    header("HTTP/1.0 405 Method Not Allowed");
    http_response_code(405);
    exit;
}

function writeToFile($file = null, $data = null) {
    if (empty($file) || $data === null) {
        error_log("writeToFile: Invalid file path or data provided. File: '" . (string)$file . "', Data type: " . gettype($data));
        return false;
    }
    
    $directory = dirname($file);
    
    if (!file_exists($directory)) {
        if (!mkdir($directory, 1777, true)) {
            error_log("writeToFile: Failed to create directory '$directory'. " ."Please check parent directory permissions or SELinux/AppArmor policies.");
            return false;
        }
    }
    
    if (is_array($data)) {
        $data = json_encode($data);
        if ($data === false) {
            error_log("writeToFile: Failed to encode data to JSON for file '$file'.");
            return false;
        }
    }
    
    $desiredFilePermissions = 1777;
    $logEntry = date('Y-m-d H:i:s') . " : " . $data;

    if (file_exists($file)) {
        $logEntry = "\n" . $logEntry;
    }

    // Attempt to write the content to the file.
    try {
        $result = file_put_contents($file, $logEntry, FILE_APPEND | LOCK_EX);
        if ($result === false) {
            error_log("writeToFile: Failed to write content to file '$file'. " ."This usually means the PHP user lacks write access. " ."Check file/directory permissions or SELinux/AppArmor policies.");
            return false;
        }
        chmod($file, $desiredFilePermissions);
        chown($file, "www-data");
        return true;
    } catch (Exception $e) {
        error_log("writeToFile: An unexpected error occurred while writing to file '$file'. " ."Message: " . $e->getMessage());
        return false;
    }
}