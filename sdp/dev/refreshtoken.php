<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.config/.funcs.php'; 
// require __dir__.'/../../.config/.mysql.php'; 
// require __dir__.'/../../.config/.mongodb.php';
// require __dir__.'/../../.config/.procedures.php';

// POST request only
if(!ReqGet()) ReqBad();

$headers = [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest',
    //'X-Authorization: '.refreshingToken()
];

$return = callAPI("GET", SDP1.'api/auth/RefreshToken', $headers );

print_j($return);
// save to redis

// {
// "token":
// "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJ0ZXN0dXNlciIsImF1ZCI6IkFDQ0VTUyIsInNjb3BlcyI6IkFETUlOI
// iwiaXNzIjoiaHR0cDovL3NpeGRlZS5jb20iLCJpYXQiOjE1Njk0OTc3NTgsImV4cCI6MTU3NDI5Nzc1OH
// 0.okOMCxGRFd1qt2OLVFFF4eDJ6aPZpLDhkNLA9STVMt9zH7fiMYaNz0S56_tJSXAtxYYq02PoQyG
// O4WBs716tCg"
// }