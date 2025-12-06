<?php
/*
db.transactions.aggregate([
    {
      $match: {
        date_column: {
          $gte: ISODate("2024-01-01"),
          $lt: ISODate("2025-01-01")
        }
      }
    },
    {
      $group: {
        _id: { $dateToString: { format: "%Y-%m", date: "$date_column" } },
        total: { $sum: "$amount" }
      }
    },
    { $sort: { _id: 1 } }
])  
*/

require __dir__.'/../../.config/.config.php'; 
require __dir__.'/../../.core/.funcs.php'; 
require __dir__.'/../../.core/.mysql.php';
require __dir__.'/../../.core/.procedures.php';

// GET request only
if(!ReqGet()) ReqBad();

$headers = getallheaders();
// 2. validate

// 3. read from mysql
$dbdata = [
    'action' => 14
];


if(isset($headers['CustomerId'])) $dbdata['customerId'] = validInt($headers['Customerid']);
if(isset($headers['Groupid'])) $dbdata['groupId'] = validInt($headers['Groupid']);

$payments = PROC(PAYMENT($dbdata))[0];
print_j($payments);