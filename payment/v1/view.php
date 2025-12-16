<?php

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php'; 
// require __dir__.'/../../.config/.mongodb.php';
require __dir__.'/../../.core/.procedures.php';

// GET request only
if(!ReqGet()) ReqBad();

$headers = array_change_key_case(getallheaders(), CASE_LOWER);
// 2. validate

// 3. read from mysql
$dbdata = [
    'action' => 5,
    'limit' => isset($_GET['limit']) ? validInt($_GET['limit']) : LIMIT
];

if(isset($_GET['paymentId'])) $dbdata['paymentId'] = validInt($_GET['paymentId']);
if(isset($headers['customerId'])) $dbdata['customerId'] = validInt($headers['customerid']);
if(isset($headers['pgroupid'])) $dbdata['pgroupId'] = validInt($headers['pgroupid']);
if(isset($_GET['starttime']) && !empty($_GET['starttime'])) $dbdata['starttime'] = $_GET['starttime'];
if(isset($_GET['endtime']) && !empty($_GET['endtime'])) $dbdata['endtime'] = $_GET['endtime'];
if(isset($_GET['amount']) && !empty($_GET['amount'])) $dbdata['amount'] = (int)$_GET['amount'];
if(isset($_GET['statusId']) && !empty($_GET['statusId'])) $dbdata['statusId'] = (int)$_GET['statusId'];
if(isset($_GET['modeId']) && !empty($_GET['modeId'])) $dbdata['modeId'] = (int)$_GET['modeId'];
if(isset($_GET['reference']) && !empty($_GET['reference'])) $dbdata['reference'] = validString($_GET['reference']);

$payments = PROC(PAYMENT($dbdata));
if(!empty($payments)) $payments = $payments[0];

$payments = array_map(function($payment){
    $timestamp = strtotime($payment['created']);
    $payment['date'] = date('Y-M-d', $timestamp);
    $payment['time'] = date('H:i', $timestamp);
    return $payment;
},$payments);

print_j($payments);