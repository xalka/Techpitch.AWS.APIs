<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php';
require __dir__.'/../../.core/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';
require __dir__.'/../../.core/.mysql.php'; 

if(!ReqDelete() && !ReqPost()) ReqBad();

$req = json_decode(file_get_contents('php://input'),1);
// Validate

// 4. Update mysql
$userId = validInt($req['id']);
$dbdata = [
    'action' => 3,
    'userId' => $userId,
    'active' => ReqDelete() ? 0 : 1,
    'adminId' => HEADERS['adminid']
];

try {
    $return1 = PROC(USER($dbdata));

    $return1 = $return1[0][0];

    if(isset($return1['updated']) && $return1['updated']==-1){
        $response = [
            'status' => 401,
            'message' => isset($return1['message']) ? $return1['message'] : 'Technical problem, please try again'
        ];

    } elseif(isset($return1['updated']) && $return1['updated']==0){
        $response = [
            'status' => 401,
            'message' => "No changes to update"
        ];         
    
    } else {

        // 5. update mongodb
        $filter = [
            '_id' => $userId
        ];
        $dbdata['updated'] = mongodate('NOW');
        $dbdata['updatedBy'] = validInt($dbdata['adminId']);
        
        if(mongoUpdate(CUSER,$filter,$dbdata)){
            $response = [
                'status' => 201
            ];          
        }
    }

} catch (Exception $e) {
    $response = [
        'status' => 401,
        'error' => $e->getMessage()
    ];    
}

// 7. Respond with a json
print_j($response);