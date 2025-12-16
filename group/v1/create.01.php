<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

if(!ReqPost()) ReqBad();

$headers = getallheaders();

$req = json_decode(file_get_contents('php://input'),1);

foreach ($req['contacts'] as $key => $contact) {
    $req['contacts'][$key]['phone'] = validPhone($contact['phone']);
}

$dbdata = [
    'action' => 1,
    'customerId' => $headers['Customerid'],
    'pgroupId' => $headers['Groupid'],
    'title' => $req['title'],
    // 'message' => $req['message']
];

try {
    // 4. Save into mysql
    $return = PROC(ContactGroup($dbdata))[0][0]; // Done

    if(isset($return['created']) && $return['created'] > 0){

        $groupId = $return['groupId'];

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