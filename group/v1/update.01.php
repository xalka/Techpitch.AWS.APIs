<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

$baseDir = dirname(__dir__,2);

require_once $baseDir.'/.config/.config.php'; 
$files = ['.funcs.php','.mysql.php','.mongodb.php','.procedures.php','KafkaHelper.php'];
foreach ($files as $file) require_once $baseDir.'/.core/'.$file;

if(!ReqPut()) ReqBad();

$headers = array_change_key_case(getallheaders(), CASE_LOWER);

// print_r($req); exit;
foreach ($req['contacts'] as $key => $contact) {
    $req['contacts'][$key]['phone'] = validPhone($contact['phone']);
}

$dbdata = [
    'action' => 3,
    'customerId' => $headers['Customerid'],
    'pgroupId' => $headers['Groupid'],
    'title' => validString($req['title']),
    'groupId' => validInt($req['id']),
    // 'message' => $req['message']
];

// print_j($req['contacts']);

try {
    // 4. Save into mysql
    $return = PROC(ContactGroup($dbdata))[0][0]; // Done

    if(isset($return['updated']) && $return['updated'] != -1){

        $groupId = $dbdata['groupId'];

        // 2. Break the contact list into chunks
        $chunks = array_chunk($req['contacts'],GROUP_CHUNKS);
        foreach ($chunks as $chunk) {
            $values = [];
            foreach ($chunk as $contact) {
                $values[] = "($groupId, {$contact['phone']}, '{$contact['fname']}', '{$contact['lname']}')";
            }
            $sql = "INSERT INTO groupContacts(groupId,phone,fname,lname) VALUES ".implode(',', $values);
            query($sql);
        }
        
        // 2. Validate


        $contacts = [
            '_id' => validInt($return['groupId']),
            'title' => $dbdata['title'],
            'contacts' => $req['contacts'],
            'pgroupId' => validInt($headers['Groupid']),
            'active' => 1,
            'created' => mongodate('NOW')
        ];

        $return2 = mongoInsert(CGROUP,$contacts);

        if($return2){
            $response = [
                'status' => 201
            ];
        }

    } else {
        $response = [
            'status' => 401,
            'error' => "Failed to save the contacts"
        ];         
    }

} catch (Exception $e) {
    $response = [
        'status' => 401,
        'error' => $e->getMessage()
    ];    
}

// 7. Respond with a json
print_j($response);