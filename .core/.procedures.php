<?php

function ContactGroup($data=null){
	$action = $data['action'];
	$id = isset($data['id']) && !empty($data['id']) ? $data['id'] : 'null';
	$groupId = isset($data['groupId'])  && !empty($data['groupId']) ? $data['groupId'] : 'null';
	$pgroupId = isset($data['pgroupId'])  && !empty($data['pgroupId']) ? $data['pgroupId'] : 'null';
	$customerId = isset($data['customerId'])  && !empty($data['customerId']) ? $data['customerId'] : 'null';
	$title = isset($data['title'])  && !empty($data['title']) ? $data['title'] : '0';
	$phone = isset($data['phone'])  && !empty($data['phone']) ? $data['phone'] : 'null';
	$fname = isset($data['fname'])  && !empty($data['fname']) ? $data['fname'] : '0';
	$lname = isset($data['lname'])  && !empty($data['lname']) ? $data['lname'] : '0';
	$descrp = isset($data['descrp'])  && !empty($data['descrp']) ? $data['descrp'] : '0';
	$active = isset($data['active'])  && !empty($data['active']) ? $data['active'] : 'null';
	$starttime = isset($data['starttime'])  && !empty($data['starttime']) ? $data['starttime'] : date(NOW,strtotime('Y-m-d'));
	$endtime = isset($data['endtime'])  && !empty($data['endtime']) ? $data['endtime'] : date(NOW,strtotime('Y-m-d'));
	$start = isset($data['start'])  && !empty($data['start']) ? $data['start'] : START;
	$limit = isset($data['limit'])  && !empty($data['limit']) ? $data['limit'] : LIMIT;

	return "CONTACTGROUP($action,$id,$groupId,$pgroupId,$customerId,'$title',$phone,'$fname','$lname','$descrp',$active,'$starttime','$endtime',$start,$limit)";
}

function Message($data=null){
	$action = $data['action'];
	$messageId = isset($data['messageId']) && !empty($data['messageId']) ? $data['messageId'] : 'null';
	$pgroupId = isset($data['pgroupId'])  && !empty($data['pgroupId']) ? $data['pgroupId'] : 'null';
	$groupId = isset($data['groupId'])  && !empty($data['groupId']) ? $data['groupId'] : 'null';
	$customerId = isset($data['customerId'])  && !empty($data['customerId']) ? $data['customerId'] : 'null';
	$adminId = isset($data['adminId'])  && !empty($data['adminId']) ? $data['adminId'] : 'null';
	$phone = isset($data['phone'])  && !empty($data['phone']) ? $data['phone'] : 'null';
	$reference = isset($data['reference'])  && !empty($data['reference']) ? $data['reference'] : '0';
	$title = isset($data['title'])  && !empty($data['title']) ? $data['title'] : '0';
	$message = isset($data['message'])  && !empty($data['message']) ? $data['message'] : '0';
	$alphanumeric = isset($data['alphanumeric'])  && !empty($data['alphanumeric']) ? $data['alphanumeric'] : '0';
	$alphanumericId = isset($data['alphanumericId'])  && !empty($data['alphanumericId']) ? $data['alphanumericId'] : 'null';
	$shortcodeId = isset($data['shortcodeId'])  && !empty($data['shortcodeId']) ? $data['shortcodeId'] : 'null';
	$offercodeId = isset($data['offercodeId'])  && !empty($data['offercodeId']) ? $data['offercodeId'] : 'null';
	$typeId = isset($data['typeId'])  && !empty($data['typeId']) ? $data['typeId'] : 'null';
	$modeId = isset($data['modeId'])  && !empty($data['modeId']) ? $data['modeId'] : '0';
	$statusId = isset($data['statusId'])  && !empty($data['statusId']) ? $data['statusId'] : 'null';
	$units = isset($data['units'])  && !empty($data['units']) ? $data['units'] : 'null';
	$delivered = isset($data['delivered'])  && !empty($data['delivered']) ? $data['delivered'] : 'null';
	$sent = isset($data['sent'])  && !empty($data['sent']) ? $data['sent'] : 'null';
	$trecipients = isset($data['recipients'])  && !empty($data['recipients']) ? $data['recipients'] : 'null';
	$tfailed = isset($data['failed'])  && !empty($data['failed']) ? $data['failed'] : 'null';
	$tdelivered = isset($data['tdelivered'])  && !empty($data['tdelivered']) ? $data['tdelivered'] : 'null';
	$succMessage = isset($data['succMessage'])  && !empty($data['succMessage']) ? $data['succMessage'] : '0';
	$errMessage = isset($data['errMessage'])  && !empty($data['errMessage']) ? $data['errMessage'] : '0';
	$blocked = isset($data['blocked'])  && !empty($data['blocked']) ? $data['blocked'] : '0';
	$scheduledAt = isset($data['scheduled'])  && !empty($data['scheduled']) ? $data['scheduled'] : date(NOW,strtotime('Y-m-d'));
	$sentAt = isset($data['sentAt'])  && !empty($data['sentAt']) ? $data['sentAt'] : date(NOW,strtotime('Y-m-d'));
	$starttime = isset($data['starttime'])  && !empty($data['starttime']) ? $data['starttime'] : date(NOW,strtotime('Y-m-d'));
	$endtime = isset($data['endtime'])  && !empty($data['endtime']) ? $data['endtime'] : date(NOW,strtotime('Y-m-d'));
	$start = isset($data['start'])  && !empty($data['start']) ? $data['start'] : START;
	$limit = isset($data['limit'])  && !empty($data['limit']) ? $data['limit'] : LIMIT;

	return "MESSAGE($action,$messageId,$pgroupId,$groupId,$customerId,$adminId,$phone,'$reference','$title','$message','$alphanumeric',$alphanumericId,$shortcodeId,$offercodeId,$typeId,'$modeId',$statusId,$units,$delivered,$sent,$trecipients,$tfailed,$tdelivered,'$succMessage','$errMessage',$blocked,'$scheduledAt','$sentAt','$starttime','$endtime',$start,$limit)";
}

function Template($data=null){
	$action = $data['action'];
	$templateId = isset($data['id']) && !empty($data['id']) ? $data['id'] : 'null';
	$pgroupId = isset($data['pgroupId'])  && !empty($data['pgroupId']) ? $data['pgroupId'] : 'null';
	$customerId = isset($data['customerId']) && !empty($data['customerId']) ? $data['customerId'] : 'null';
	$adminId = isset($data['adminId'])  && !empty($data['adminId']) ? $data['adminId'] : 'null';
	$title = isset($data['title'])  && !empty($data['title']) ? $data['title'] : 'null';
	$message = isset($data['message'])  && !empty($data['message']) ? $data['message'] : 'null';
	$active = isset($data['active'])  && !empty($data['active']) ? $data['active'] : 'null';
	$starttime = isset($data['starttime'])  && !empty($data['starttime']) ? $data['starttime'] : date(NOW,strtotime('Y-m-d'));
	$endtime = isset($data['endtime'])  && !empty($data['endtime']) ? $data['endtime'] : date(NOW,strtotime('Y-m-d'));
	$start = isset($data['start'])  && !empty($data['start']) ? $data['start'] : START;
	$limit = isset($data['limit'])  && !empty($data['limit']) ? $data['limit'] : LIMIT;

	return "TEMPLATE($action,$templateId,$pgroupId,$customerId,$adminId,'$title','$message',$active,'$starttime','$endtime',$start,$limit)";
}

function Payment($data = null) {
	$action = $data['action'];
    $paymentId = formatValue($data['paymentId'] ?? null);
    $pgroupId = formatValue($data['pgroupId'] ?? null);
    $customerId = formatValue($data['customerId'] ?? null);
    $messageId = formatValue($data['messageId'] ?? null);
    $adminId = formatValue($data['adminId'] ?? null);
    $phone = formatValue($data['phone'] ?? 0);
    $amount = formatValue($data['amount'] ?? 0);
    $rate = formatValue($data['rate'] ?? 0);
    $units = formatValue($data['units'] ?? 0);

    $fname = formatValue($data['fname'] ?? null, true);
    $mname = formatValue($data['mname'] ?? null, true);
    $lname = formatValue($data['lname'] ?? null, true);
    $reference = formatValue($data['reference'] ?? '', true);
    $MerchantRequestID = formatValue($data['MerchantRequestID'] ?? '', true);
    $CheckoutRequestID = formatValue($data['CheckoutRequestID'] ?? '', true);

    $modeId = formatValue($data['modeId'] ?? null);
    $statusId = formatValue($data['statusId'] ?? null);
    $posted = formatValue($data['posted'] ?? 0);
    $description = formatValue($data['description'] ?? '', true);
	
    $thirdpartyTime = isset($data['thirdpartyTime']) && !empty($data['thirdpartyTime']) ? "'" . datetime($data['thirdpartyTime']) . "'" : 'NULL';
    $starttime = isset($data['starttime']) && !empty($data['starttime']) ? "'" . datetime($data['starttime']) . "'" : 'NULL';
    $endtime = isset($data['endtime']) && !empty($data['endtime']) ? "'" . datetime($data['endtime']) . "'" : 'NULL';

    $start = (int) ($data['start'] ?? START);
    $limit = (int) ($data['limit'] ?? LIMIT);

    return "PAYMENT($action, $paymentId, $pgroupId, $customerId, $messageId, $adminId, $phone, $amount, $rate, $units, $fname, $mname, $lname, $reference, $MerchantRequestID, $CheckoutRequestID, $modeId, $statusId, $posted, $description, $thirdpartyTime, $starttime, $endtime, $start, $limit)";
}

function Customer($data=null){
	$action = $data['action'];
	$customerId = isset($data['customerId'])  && !empty($data['customerId']) ? $data['customerId'] : 'null';
	$email = isset($data['email'])  && !empty($data['email']) ? $data['email'] : '0';
	$phone = isset($data['phone'])  && !empty($data['phone']) ? $data['phone'] : '0';
	$cname = isset($data['cname'])  && !empty($data['cname']) ? $data['cname'] : '0';
	$fname = isset($data['fname'])  && !empty($data['fname']) ? $data['fname'] : '0';
	$lname = isset($data['lname'])  && !empty($data['lname']) ? $data['lname'] : '0';
	$active = isset($data['active'])  && !empty($data['active']) ? $data['active'] : 'null';
	$passreset = isset($data['passreset'])  && !empty($data['passreset']) ? $data['passreset'] : 'null';
	$pass = isset($data['password'])  && !empty($data['password']) ? $data['password'] : 'null';
	$pcode = isset($data['pcode'])  && !empty($data['pcode']) ? $data['pcode'] : 'null';
	$ecode = isset($data['ecode'])  && !empty($data['ecode']) ? $data['ecode'] : 'null';
	$img = isset($data['img'])  && !empty($data['img']) ? $data['img'] : 'null';
	$address = isset($data['address'])  && !empty($data['address']) ? $data['address'] : 'null';
	$groupId = isset($data['groupId'])  && !empty($data['groupId']) ? $data['groupId'] : 'null';
	$typeId = isset($data['typeId'])  && !empty($data['typeId']) ? $data['typeId'] : 'null';
	$vtype = isset($data['vtype'])  && !empty($data['vtype']) ? $data['vtype'] : '0';
	$roleId = isset($data['roleId'])  && !empty($data['roleId']) ? $data['roleId'] : 'null';
	$adminId = isset($data['adminId'])  && !empty($data['adminId']) ? $data['adminId'] : 'null';
	$alphanumericId = isset($data['alphanumericId'])  && !empty($data['alphanumericId']) ? $data['alphanumericId'] : 'null';
	$alphanumeric = isset($data['alphanumeric'])  && !empty($data['alphanumeric']) ? $data['alphanumeric'] : '0';
	$passExpire = isset($data['expiretime'])  && !empty($data['expiretime']) ? $data['expiretime'] : date('Y-m-d');
	$starttime = isset($data['starttime'])  && !empty($data['starttime']) ? $data['starttime'] : date('Y-m-d');
	$endtime = isset($data['endtime'])  && !empty($data['endtime']) ? $data['endtime'] : date('Y-m-d');
	$start = isset($data['start'])  && !empty($data['start']) ? $data['start'] : START;
	$start = isset($data['start'])  && !empty($data['start']) ? $data['start'] : START;
	$limit = isset($data['limit'])  && !empty($data['limit']) ? $data['limit'] : LIMIT;

	return "CUSTOMER($action,$customerId,'$email','$phone','$cname','$fname','$lname',$active,$passreset,'$pass','$pcode','$ecode','$img','$address',$groupId,$typeId,'$vtype',$roleId,$adminId,$alphanumericId,'$alphanumeric','$passExpire','$starttime','$endtime',$start,$limit)";
}

function User($data=null){
	$action = $data['action'];
	$userId = isset($data['userId']) && !empty($data['userId']) ? $data['userId'] : 'null';
	$email = isset($data['email']) && !empty($data['email']) ? $data['email'] : '0';
	$phone = isset($data['phone']) && !empty($data['phone']) ? $data['phone'] : '0';
	$fname = isset($data['fname']) && !empty($data['fname']) ? $data['fname'] : '0';
	$lname = isset($data['lname']) && !empty($data['lname']) ? $data['lname'] : '0';
	$active = isset($data['active']) && !empty($data['active']) ? $data['active'] : 'null';
	$passreset = isset($data['passreset']) && !empty($data['passreset']) ? $data['passreset'] : 'null';
	$pass = isset($data['password']) && !empty($data['password']) ? $data['password'] : 'null';
	$code = isset($data['code']) && !empty($data['code']) ? $data['code'] : 'null';
	$typeId = isset($data['typeId']) && !empty($data['typeId']) ? $data['typeId'] : 'null';
	$roleId = isset($data['roleId']) && !empty($data['roleId']) ? $data['roleId'] : 'null';
	$adminId = isset($data['adminId']) && !empty($data['adminId']) ? $data['adminId'] : 'null';
	$passExpire = isset($data['expiretime']) && !empty($data['expiretime']) ? $data['expiretime'] : date('Y-m-d');
	$starttime = isset($data['starttime']) && !empty($data['starttime']) ? $data['starttime'] : date('Y-m-d');
	$endtime = isset($data['endtime']) && !empty($data['endtime']) ? $data['endtime'] : date('Y-m-d');
	$start = isset($data['start']) && !empty($data['start']) ? $data['start'] : START;
	$limit = isset($data['limit']) && !empty($data['limit']) ? $data['limit'] : LIMIT;

	return "USERS($action,$userId,'$email','$phone','$fname','$lname',$active,$passreset,'$pass','$code',$typeId,$roleId,$adminId,'$passExpire','$starttime','$endtime',$start,$limit)";
}