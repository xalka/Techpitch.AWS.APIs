/*
Date : 7 August 2025
Title : Migrate data from tpsysold to tpsys
Author : Joshua Musyoka
*/


insert ignore into tpsyswithdata.customer(customerId,email,phone,fname,lname,nationalId,pass,customertypeId,created)
SELECT id,email,phone_number,first_name,last_name,id_number,passwd,1, date_registered
FROM tpsysold.TP_kannel_external_cust_registration;

INSERT INTO tpsyswithdata.groupings(groupId,adminId,active)
select customerId,customerId,'1' from tpsyswithdata.customer;

UPDATE tpsyswithdata.groupings g
JOIN tpsysold.TP_kannel_external_cust_registration c ON c.id = g.groupId
SET g.units = c.sms_credit
where
	c.id != 1264;
-- Account 1264 should be manually updated

INSERT INTO participant(groupId,customerId,active)
select customerId,customerId,'1' from tpsyswithdata.customer;

INSERT INTO customersroles(customerId,groupId,roleId)
select customerId,customerId,1 from tpsyswithdata.customer;

/*
Cleaning the new schema
*/

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE messagesRecipientGroup;
TRUNCATE TABLE messagesRecipients;
TRUNCATE TABLE groupunitbalance;
TRUNCATE TABLE messages;
TRUNCATE TABLE alphanumeric;
TRUNCATE TABLE groupContacts;
TRUNCATE TABLE contactGroups;
TRUNCATE TABLE customersroles;
TRUNCATE TABLE offercode;
TRUNCATE TABLE offercodekeyword;
TRUNCATE TABLE participant;
TRUNCATE TABLE payments;
TRUNCATE TABLE template;
TRUNCATE TABLE groupings;
TRUNCATE TABLE customer;

SET FOREIGN_KEY_CHECKS = 1;


/*
Template
*/

insert ignore into tpsyswithdata.template(templateId,title,template,customerId,groupId,created)
SELECT id, name, content, created_by, created_by, created_at
FROM tpsysold.TP_kannel_sms_templates
WHERE created_by REGEXP '^[0-9]+$';

SELECT id, name, content, updated_by, updated_by, updated_at
FROM tpsysold.TP_kannel_sms_templates 
WHERE 
	created_at NOT REGEXP '^[0-9]+$'
    and created_by NOT REGEXP '^[0-9]+$'
    and updated_by REGEXP '^[0-9]+$';
-- In the where, alternate between created_by, created_at and updated_by to maximise the results


/*
Contact Groups
*/

-- Script 1
INSERT INTO tpsyswithdata.contactGroups(groupId, pgroupId, title, customerId, active, created)
SELECT id, created_by, group_name, created_by, '1',
    CASE
        WHEN created_at IS NULL OR created_at = '' THEN NOW()
        WHEN created_at REGEXP '^[0-9]+$' THEN NOW()
        WHEN created_at REGEXP '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01]) (2[0-3]|[01][0-9]):([0-5][0-9]):([0-5][0-9])$'
             AND STR_TO_DATE(created_at, '%Y-%m-%d %H:%i:%s') IS NOT NULL
        THEN STR_TO_DATE(created_at, '%Y-%m-%d %H:%i:%s')
        ELSE NOW()
    END AS created_at
FROM tpsysold.TP_bis_contact_groups t
WHERE EXISTS (
    SELECT 1 FROM tpsyswithdata.groupings g WHERE g.groupId = t.created_by
);
-- Script 2
INSERT INTO tpsyswithdata.contactGroups(groupId, pgroupId, title, customerId, active, created)
SELECT id, created_at, group_name, created_at, '1', edited_by
FROM tpsysold.TP_bis_contact_groups
WHERE
	created_by NOT REGEXP '^[0-9]+$'
    and created_at REGEXP '^[0-9]+$';

-- Contacts into groups
-- use script for looping
INSERT INTO tpsyswithdata.groupContacts (groupContactId, groupId, phone, fname, created)
SELECT id, group_id, user_id, TRIM(username),
       CASE
           WHEN created_at IS NULL OR created_at = '' THEN NOW()
           WHEN created_at REGEXP '^[0-9]+$' THEN NOW()
           WHEN created_at REGEXP '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01]) (2[0-3]|[01][0-9]):([0-5][0-9]):([0-5][0-9])$'
                AND STR_TO_DATE(created_at, '%Y-%m-%d %H:%i:%s') IS NOT NULL
           THEN STR_TO_DATE(created_at, '%Y-%m-%d %H:%i:%s')
           ELSE NOW()
       END AS created_at
FROM tpsysold.TP_kannel_user_groups c
WHERE EXISTS (
    SELECT 1 FROM tpsyswithdata.contactGroups g WHERE g.groupId = c.group_id
)
LIMIT 0 OFFSET 5;


/*
Alphanumeric
*/

insert IGNORE into tpsyswithdata.alphanumeric(alphanumericId,title,userId,customerId,groupId,created)
SELECT distinct id,name,NULL,user_id,user_id,created_at 
FROM tpsysold.TP_kannel_user_shortcodes
WHERE
	-- name = 'TECHXAL'
    user_id != 1003;


/*
Users
*/
insert into tpsyswithdata.users(userId,email,phone,fname,lname,active,pass,created)
SELECT id,
	  CASE
		WHEN Email = '0' THEN NULL
		ELSE Email
	  END AS Email,
	  CASE
		WHEN phone = '0' THEN NULL
		ELSE phone
	  END AS phone,
    first_Name,last_Name,1,password,date_registered 
FROM tpsysold.TP_users_users;



/*
Payments
*/


INSERT INTO tpsyswithdata.payments(paymentId, statusId, adminId, customerId, groupId, amount, units, phone, reference, posted, posteddate, created, modeId)
SELECT 
    Trans_ID,
    3,
    inserted_by,
    uid,
    uid,
    Sum_Transaction,
    credit_bought,
    User_ID,
    external_id,
    1,
    Trans_Dtime,
    Trans_Dtime,
    IF(Source = 1 OR inserted_by IS NULL, 2, 3)
FROM tpsysold.TP_billing_transaction b
WHERE EXISTS (
    SELECT 1 FROM tpsyswithdata.groupings g WHERE g.groupId = b.uid
);





/*
Messages
*/

-- SELECT * FROM tpsys.messages;
-- set @typeId = 2, @statusId = 6;

insert into tpsyswithdata.messages(messageId,reference,title,content,typeId,statusId,customerId,groupId,scheduled_time,created, alphanumericId)
SELECT -- * 
	id, sms_id, title, message, 2, 6, created_by, created_by, scheduled_time, created_at,
    (SELECT alphanumericId FROM tpsyswithdata.alphanumeric where title = sender) alphanumeric
FROM tpsysold.TP_kannel_external_cust_sms sms
-- JOIN tpsysold.TP_kannel_sms_status st on st.id = sms.status
where 
	1 = 1
    -- and status = 3
	-- and sms.created_by = 1106;
    and (SELECT alphanumericId FROM tpsyswithdata.alphanumeric where title = sender) is not null
-- order by sms_id desc
;