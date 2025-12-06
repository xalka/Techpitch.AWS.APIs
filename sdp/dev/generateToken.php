<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
// require __dir__.'/../../.config/.mysql.php'; 
// require __dir__.'/../../.config/.mongodb.php';
// require __dir__.'/../../.config/.procedures.php';

// POST request only
# if(!ReqPost()) ReqBad();

$headers = [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-Authorization: Bearer',
];

$request = [
    'username' => 'TechPitchAPI',
    // 'password' => 'TechPitch$321',
    'password' => 'Admin@123 '  
];

// echo SDP1.'api/auth/login';
// print_j($headers);
// print_j($request);
// exit;
$return = callAPI("POST", SDP1.'api/auth/login', $headers, $request);

print_r($return);
// save to redis

// {
//     "msg": "You have been Authenticated to access this protected API System.",
//     "token":
// "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJ0ZXN0dXNlciIsImF1ZCI6IkFDQ0VTUyIsInNjb3BlcyI6IkFETUlOIi
// wiaXNzIjoiaHR0cDovL3NpeGRlZS5jb20iLCJpYXQiOjE1Njk0OTc1MjksImV4cCI6MTU3NDI5NzUyOX0.-
// u2Db8OSDhtITMoFqIZYTgs6u4Ib_voynEA6k7ZwiqJqaPQ1_CnUaARxeaoSpC_BC-78_k-
// rzOr3v2Jdb9_KaA",
//     "refreshToken":
// "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJ0ZXN0dXNlciIsImF1ZCI6IlJFRlJFU0giLCJzY29wZXMiOiJBRE1J
// TiIsImlzcyI6Imh0dHA6Ly9zaXhkZWUuY29tIiwianRpIjoiZGIzOTk4OTYtMTU0ZS00ZDFjLTg1NmYtNTUy
// MDE2MDU3MDVkIiwiaWF0IjoxNTY5NDk3NTI5LCJleHAiOjE1ODAyOTc1Mjl9.uD7fvaMigBI0a2GC00fte
// qtTx79Elil1CFxRtXz5CTs1qRhJYUVsD0ZjF5Q13J9btY-5ppuzFDqDFkFfUpZAMw"
// }