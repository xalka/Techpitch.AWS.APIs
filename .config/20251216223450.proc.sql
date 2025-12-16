/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.22-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: tpsyswithdata
-- ------------------------------------------------------
-- Server version	10.6.22-MariaDB-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_groups_units_after_insert
AFTER INSERT ON groupunitbalance
FOR EACH ROW
BEGIN
  UPDATE groupings 
  SET units = NEW.closingbalance 
  WHERE groupings.groupId = NEW.groupId;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping routines for database 'tpsyswithdata'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `CalculatePrice` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `CalculatePrice`(amount INT) RETURNS decimal(10,2)
    SQL SECURITY INVOKER
BEGIN
    DECLARE priced DECIMAL(10,2);

    -- Find the price based on the unit value from the price_table
    SELECT Price INTO priced
    FROM pricing
    WHERE amount BETWEEN `pFrom` AND `pTo`
    LIMIT 1;

    -- Return the calculated price
    RETURN priced;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `ValidString` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`localhost` FUNCTION `ValidString`(input VARCHAR(255)) RETURNS varchar(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci
    SQL SECURITY INVOKER
BEGIN
	RETURN CASE WHEN input IN ('0', '', 'null') THEN NULL ELSE input END;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `CONTACTGROUP` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CONTACTGROUP`(
	IN inAction INT,
	IN inId INT, 
    IN inGroupId INT,
    IN inPGroupId INT,
    IN inCustomerId INT,
	IN inTitle VARCHAR(100),
    -- IN inTypeId INT,
	IN inPhone BIGINT,
    IN inFname VARCHAR(255),
    IN inLname VARCHAR(255),
    IN inDescp VARCHAR(255),
    IN inActive TINYINT,
    IN inStartDate DATETIME,
    IN inEndDate DATETIME,
    IN inStart INT, 
    IN inLimit INT 
)
PROC: BEGIN

	DECLARE dTitle VARCHAR(255) DEFAULT NULL;
	DECLARE dGroupId,dCustomerId INT DEFAULT NULL;
    -- DECLARE dTime DATETIME DEFAULT NULL;
    
    DECLARE dRollBack BOOL DEFAULT 0;
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET dRollBack = 1;     

	CASE inAction
		-- create contact group 		
		WHEN 1 THEN
            SET inTitle = if(inTitle='0' OR inTitle='' OR inTitle='null',null,inTitle);
            
            SELECT groupId INTO dGroupId FROM contactGroups WHERE title = inTitle AND pgroupId = inPGroupId;
            
			IF NOT ISNULL(dGroupId) THEN
				SELECT 'The group title already exists' message, 0 created;
				LEAVE PROC;
            END IF;
            
            SET inDescp = if(inDescp='0' OR inDescp='' OR inDescp='null',null,inDescp);
            
            INSERT INTO contactGroups(pgroupId,title,descrp,customerId)
			VALUE(inPGroupId,inTitle,inDescp,inCustomerId);
            
            SELECT ROW_COUNT() created, LAST_INSERT_ID() groupId;
            
		-- delete group
        WHEN 2 THEN
            START TRANSACTION;
				DELETE FROM groupContacts WHERE groupId = inGroupId;
				DELETE FROM contactGroups WHERE groupId = inGroupId AND pgroupId = pgroupId;
                
			IF dRollBack THEN
				ROLLBACK;
				SELECT 'Technical problem' message, 0 deleted;
			ELSE 
				COMMIT;
				SELECT 1 deleted;
			END IF;                
			LEAVE PROC;
            
		-- update contact group 		
		WHEN 3 THEN
            SET inTitle = if(inTitle='0' OR inTitle='' OR inTitle='null',null,inTitle);
            
            SELECT groupId INTO dGroupId FROM contactGroups WHERE title = inTitle AND pgroupId = inPGroupId AND groupId <> inGroupId;
            
			IF NOT ISNULL(dGroupId) THEN
				SELECT 'The group title already exists' message, 0 created;
				LEAVE PROC;
            END IF;
            
            SET inDescp = if(inDescp='0' OR inDescp='' OR inDescp='null',null,inDescp);
            
            -- INSERT INTO contactGroups(pgroupId,title,descrp,customerId)
            UPDATE contactGroups 
				SET title = inTitle, 
                descrp = inDescp, 
                customerId = inCustomerId
			WHERE
				pgroupId = inPGroupId
                AND groupId = inGroupId;
			-- VALUE(inPGroupId,inTitle,inDescp,inCustomerId);
            
            SELECT ROW_COUNT() updated; -- , LAST_INSERT_ID() groupId;    
            
		-- Insert group contacts
        WHEN 4 THEN
			INSERT IGNORE INTO groupContacts(groupId,pgroupId,phone,fname,lname)
            VALUE(inGroupId,inPGroupId,inPhone,inFname,inLname);
            SELECT last_insert_id() id, row_count() created;
			LEAVE PROC;
            
		-- Update group contacts
        WHEN 5 THEN
			/* UPDATE groupContacts 
				SET phone = inPhone,
                    fname = inFname,
                    lname = inLname
            WHERE
				id = inId
				AND pgroupId = inPGroupId
                AND groupId = inGroupId;
				
            SELECT row_count() updated; */
            
            SET inPhone = ValidString(inPhone);
            SET inFname = ValidString(inFname);
            SET inLname = ValidString(inLname);
            
            IF inId IS NOT NULL AND inId <> 0 THEN
				-- Try updating first
				UPDATE groupContacts 
					SET 
						phone = inPhone,
						fname = inFname,
						lname = inLname
				WHERE
					id = inId
					AND pgroupId = inPGroupId
					AND groupId = inGroupId;
                    
				SELECT inId id, ROW_COUNT() updated, 'updated' message;
                LEAVE PROC;
            END IF;
            -- select inId, inPhone, inFname, inLname, inPGroupId, inGroupId; leave proc;
			-- If here → no record was updated, so insert it
			INSERT IGNORE INTO groupContacts (phone, fname, lname, pgroupId, groupId)
			VALUES (inPhone, inFname, inLname, inPGroupId, inGroupId);           

			SELECT LAST_INSERT_ID() id, 'inserted' message;
            
			LEAVE PROC;            
            
		ELSE
			SELECT 'Out of actions' denoted; 
            LEAVE PROC; 
        
	END CASE;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `CUSTOMER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CUSTOMER`(
	IN inAction INT,
	IN inCustomerId INT, 
	IN inEmail VARCHAR(100),
	IN inPhone VARCHAR(100),
	IN inCname VARCHAR(100),
	IN inFname VARCHAR(100),
    IN inLname VARCHAR(255),
    IN inActiveId TINYINT,
    IN inResetPass TINYINT, 
	IN inPass VARCHAR(255),
	IN inPcode VARCHAR(255),
	IN inEcode VARCHAR(255),
	IN inImg VARCHAR(255),
	IN inAddress VARCHAR(255),
    IN inGroupId INT,
    IN inTypeId INT,
    IN inVType VARCHAR(255),
	IN inRoleId INT,
	IN inAdminId INT,
	IN inAlphanumericId INT,
	IN inAlphanumeric VARCHAR(255), -- location
    IN inPassExpire DATETIME,
    IN inStartDate DATETIME,
    IN inEndDate DATETIME,
    IN inStart INT, 
    IN inLimit INT 
)
PROC: BEGIN

	DECLARE dPhone,dEmail,dECode,dFname VARCHAR(255) DEFAULT NULL;
	DECLARE dPCode,dCustomerId,dGroupId,dNew INT DEFAULT NULL;
    -- DECLARE dTime DATETIME DEFAULT NULL;
    
    DECLARE dRollBack BOOL DEFAULT 0;
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET dRollBack = 1;     

	CASE inAction
		-- create customer		
		WHEN 1 THEN
            SET inEmail = if(inEmail='0' OR inEmail='' OR inEmail='null',null,inEmail);
            SET inPhone = if(inPhone='0' OR inPhone='' OR inPhone='null',null,inPhone);
            
            SELECT customerId INTO dCustomerId FROM customer WHERE phone = inPhone or email = inEmail;
            
			IF NOT ISNULL(dCustomerId) THEN
				SELECT email INTO dEmail FROM customer WHERE email = inEmail;
				SELECT phone INTO dPhone FROM customer WHERE phone = inPhone;
				SELECT 'The customer already exists' message, dPhone phone, dEmail email, 0 created, dCustomerId customerId;
				LEAVE PROC;
            END IF;
            
            SET inCname = if(inCname='0' OR inCname='' OR inCname='null',null,inCname);
            SET inFname = if(inFname='0' OR inFname='' OR inFname='null',null,inFname);
            SET inLname = if(inLname='0' OR inLname='' OR inLname='null',null,inLname);
            SET inPass = if(inPass='0' OR inPass='' OR inPass='null',null,inPass);
            SET inPcode = if(inPcode='0' OR inPcode='' OR inPcode='null',null,inPcode);
            SET inEcode = if(inEcode='0' OR inEcode='' OR inEcode='null',null,inEcode);
            -- SET inVType = if(inVType='0' OR inVType='' OR inVType='null',null,inVType);
            -- SET inRoleIds = if(inRoleIds='0' OR inRoleIds='' OR inRoleIds='null',null,inRoleIds);
            
            START TRANSACTION;
				INSERT INTO customer(email,phone,cname,fname,lname,pass,pCode,eCode,customerTypeId)
				VALUE(inEmail,inPhone,inCname,inFname,inLname,inPass,inPcode,inEcode,inTypeId);
				-- select inEmail,inPhone,inCname,inFname,inLname,inPass,inPcode,inEcode,inTypeId;
				SET dCustomerId = LAST_INSERT_ID();
				
				-- grouping
				INSERT INTO groupings(adminId,active)
				VALUE(dCustomerId,'1');
				SET dGroupId = LAST_INSERT_ID();
				
				-- participants
				INSERT INTO participant(groupId,customerId,active)
				VALUE(dGroupId,dCustomerId,'1');
                
                -- roles
                INSERT INTO customersroles(customerId,groupId,roleId)
                VALUE(dCustomerId,dGroupId,inRoleId);
                -- INSERT INTO customersroles(customerId,groupId,roleId)
                -- VALUE(dCustomerId,dGroupId,inRoleId);                
				
				-- SELECT dCustomerId id, dGroupId groupId, ROW_COUNT() created;
                
			IF dRollBack THEN
				ROLLBACK;
				SELECT 'There is a technical problem' message, 0 created;
			ELSE 
				COMMIT;	
				-- SELECT dCustomerId id, dGroupId groupId, inRoleId roleId, 'admin' role, 1 created;
				SELECT roleId, title, dCustomerId id, dGroupId groupId, 1 created
				FROM roles
				WHERE roleId = inRoleId;                  
			END IF;            
            LEAVE PROC;
        
        -- verify customer
		WHEN 2 THEN
            SET inEmail = if(inEmail='0' OR inEmail='' OR inEmail='null',null,inEmail);
            SET inPhone = if(inPhone='0' OR inPhone='' OR inPhone='null',null,inPhone);
            SET inPcode = if(inPcode='0' OR inPcode='' OR inPcode='null',null,inPcode);
            SET inEcode = if(inEcode='0' OR inEcode='' OR inEcode='null',null,inEcode);
            SET inVType = if(inVType='0' OR inVType='' OR inVType='null',null,inVType); -- select inVType, inPcode, inEcode; leave proc;
            
			UPDATE customer SET pCode = null, eCode = null, verificationTime = now(), verificationType = inVType, verified = 1
            WHERE
				verified = 0 
                AND pCode = inPcode
                AND (phone = inPhone or email = inEmail);
                -- AND if( inPcode, (pCode=inPcode and phone=inPhone), 1)
                -- AND if(inEcode, (eCode=inEcode and email=inEmail), 1);
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
		-- update customer forgotten password
        WHEN 3 THEN
            SET inPcode = if(inPcode='0' OR inPcode='' OR inPcode='null',null,inPcode);
            SET inEcode = if(inEcode='0' OR inEcode='' OR inEcode='null',null,inEcode);        
			UPDATE customer SET passreset = 1, pCode = inPcode, eCode = inEcode
            WHERE
				customerId = inCustomerId;
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
		-- update customer password
        WHEN 4 THEN
            SET inPcode = if(inPcode='0' OR inPcode='' OR inPcode='null',null,inPcode);
            SET inEcode = if(inEcode='0' OR inEcode='' OR inEcode='null',null,inEcode);        
			UPDATE customer SET passreset = 0, pCode = null, eCode = null, pass = inPass
            WHERE
				customerId = inCustomerId
                AND passreset = 1
                AND pCode = inPcode
                AND eCode = inEcode;
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
		-- create new customer under certain group
		WHEN 5 THEN
            SET inEmail = if(inEmail='0' OR inEmail='' OR inEmail='null',null,inEmail);
            SET inPhone = if(inPhone='0' OR inPhone='' OR inPhone='null',null,inPhone);
            
			SELECT c.customerId INTO dCustomerId 
			FROM customer c
			JOIN participant p ON p.customerId = c.customerId
			WHERE
				p.groupId = inGroupId
				AND (c.email = inEmail OR c.phone = inPhone);
            
			IF NOT ISNULL(dCustomerId) THEN
				SELECT email INTO dEmail FROM customer WHERE email = inEmail;
				SELECT phone INTO dPhone FROM customer WHERE phone = inPhone;
				SELECT 'The customer already exists' message, dCustomerId, inGroupId, dPhone phone, dEmail email, 0 created, dCustomerId customerId;
				LEAVE PROC;
            END IF;
            
            -- check if the customer exists at all
            IF((select count(1) from customer where phone = inPhone or email = inEmail) > 1) THEN
				SELECT 'Contact the customer for clear details' message, 0 created;
			ELSE
				SELECT customerId INTO dCustomerId FROM customer WHERE phone = inPhone or email = inEmail;
            END IF;

            -- SET inCname = if(inCname='0' OR inCname='' OR inCname='null',null,inCname);
            SET inFname = ValidString(inFname);
            SET inLname = ValidString(inLname);
            SET inPass = ValidString(inPass);
            -- SET inPcode = if(inPcode='0' OR inPcode='' OR inPcode='null',null,inPcode);
            -- SET inEcode = if(inEcode='0' OR inEcode='' OR inEcode='null',null,inEcode);
            -- SET inVType = if(inVType='0' OR inVType='' OR inVType='null',null,inVType);
            -- SET inRoleIds = if(inRoleIds='0' OR inRoleIds='' OR inRoleIds='null',null,inRoleIds);
            
            START TRANSACTION;
				IF dCustomerId IS NULL THEN
					INSERT INTO customer(email,phone,fname,lname,pass)
					VALUE(inEmail,inPhone,inFname,inLname,inPass);
					SET dCustomerId = LAST_INSERT_ID(), dNew = 1;
				END IF;
				
				-- participants
				INSERT INTO participant(groupId,customerId)
				VALUE(inGroupId,dCustomerId);
                
                -- roles
                INSERT INTO customersroles(customerId,groupId,roleId,adminId)
                VALUE(dCustomerId,inGroupId,inRoleId,inCustomerId);
                
			IF dRollBack THEN
				ROLLBACK;
				SELECT 'There is a technical problem' message, 0 created;
			ELSE
				COMMIT;	
				-- SELECT dCustomerId id, inGroupId groupId, 1 created, dNew newly;
				SELECT roleId, title, dCustomerId id, inGroupId groupId, 1 created, dNew newly
				FROM roles
				WHERE roleId = inRoleId;               
			END IF;
            LEAVE PROC;
            
		-- customers groupings 
        WHEN 6 THEN
            -- SELECT groupId FROM participant WHERE customerId = inCustomerId;
			SELECT r.roleId, r.title role, cr.groupId, cr.active
			FROM customersroles cr
			JOIN roles r ON r.roleId = cr.roleId
			WHERE customerId = inCustomerId;            
			LEAVE PROC;
            
		-- customer role per group
        WHEN 7 THEN
			SELECT r.roleId, r.title
			FROM customersroles cr
			JOIN roles r ON r.roleId = cr.roleId
			WHERE 
				customerId = inCustomerId 
				AND groupId = inGroupId;        
			LEAVE PROC;
            
		-- group accounts
        WHEN 8 THEN
			SELECT g.groupId, g.active, c.customerId, c.fname, c.lname, c.email, c.phone, g.created
			FROM groupings g
			JOIN customer c ON c.customerId = g.adminId;
			LEAVE PROC;
            
		-- customers
		WHEN 9 THEN
			SELECT c.email, c.phone, c.cname, c.fname, c.lname, c.active, c.passreset, c.pass, ct.typeId, ct.type, c.created
			FROM customer c
			JOIN customertype ct ON ct.typeId = c.customertypeId
            LIMIT inStart,inLimit;
			LEAVE PROC;
            
		-- alphanumeric list
        WHEN 10 THEN
			SET inAlphanumeric = validString(inAlphanumeric);
            
			SELECT a.alphanumericId _id, a.title, a.active, g.groupId, c.customerId, c.cname, c.fname, c.lname, c.active, ct.type, g.units, a.created
			FROM alphanumeric a
			JOIN groupings g ON g.groupId = a.groupId
			JOIN customer c ON c.customerId = g.adminId
			JOIN customertype ct ON ct.typeId = c.customertypeId
            WHERE
				if( inAlphanumericId, a.alphanumericId = inAlphanumericId, 1)
                AND if(inAlphanumeric, a.title = inAlphanumeric, 1)          
            ORDER BY a.created DESC
            LIMIT inStart,inLimit;
			LEAVE PROC;
            
		-- update customer under certain group
        WHEN 11 THEN
            SET inEmail = ValidString(inEmail), 
				inPhone = ValidString(inPhone), 
                inFname = ValidString(inFname), 
                inLname = ValidString(inLname);
            
			SELECT MAX(CASE WHEN email = inEmail THEN email END), MAX(CASE WHEN phone = inPhone THEN phone END) INTO dEmail, dPhone
			FROM customer 
			WHERE 
				customerId != inCustomerId 
                AND (email = inEmail OR phone = inPhone);

			IF dEmail IS NOT NULL OR dPhone IS NOT NULL THEN
				SELECT 'The customer already exists' message, COALESCE(dPhone, NULL) phone, COALESCE(dEmail, NULL) email, 0 updated;
				LEAVE PROC;
			END IF;            
            
			IF NOT EXISTS(SELECT 1 FROM participant WHERE groupId = inGroupId AND customerId = inCustomerId) THEN
				SIGNAL SQLSTATE '45000';
				SELECT 'The customer does not exist' message, 0 updated, MYSQL_ERRNO = 1001;
                LEAVE PROC;
			END IF;
-- select inCustomerId,inGroupId,inRoleId,inAdminId,inEmail,inPhone,inFname,inLname; leave proc;
			UPDATE customersroles SET 
				roleId = inRoleId, 
                adminId = inAdminId
                -- email = inEmail, 
                -- phone = inPhone, 
                -- fname = inFname, 
                -- lname = inLname
			WHERE
				customerId = inCustomerId
				AND groupId = inGroupId;
				-- AND active = 1;
                
			UPDATE customer SET email = inEmail, phone = inPhone, fname = inFname, lname = inLname
			WHERE
				customerId = inCustomerId;     
            SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
		-- suspend customer
        WHEN 12 THEN
			IF NOT EXISTS(SELECT 1 FROM participant WHERE groupId = inGroupId AND customerId = inCustomerId) THEN
				SIGNAL SQLSTATE '45000';
				SELECT 'The customer does not exist' message, 0 updated, MYSQL_ERRNO = 1001;
                LEAVE PROC;
			END IF;
            
            UPDATE customer SET active = '0' WHERE customerId = inCustomerId;
            UPDATE participant SET active = '0' WHERE groupId = inGroupId AND customerId = inCustomerId;
            SELECT ROW_COUNT() suspended;
			LEAVE PROC;
            
		-- create alphanumeric under account
        WHEN 13 THEN
			SET inAlphanumeric = ValidString(inAlphanumeric);
            
			IF EXISTS(select 1 from alphanumeric where title = inAlphanumeric) THEN
				SELECT 'The alphanumeric already exists' message, 0 created;
				LEAVE PROC;
            END IF;
            -- select inAlphanumeric,inAdminId,inCustomerId,inGroupId; leave proc;
			INSERT INTO alphanumeric(title,userId,customerId,groupId)
            VALUE(inAlphanumeric,inAdminId,inCustomerId,inGroupId);
            
			SELECT a.alphanumericId, a.title, c.cname, c.fname, c.lname, g.groupId, c.customerId, a.active, a.created
			FROM alphanumeric a
			JOIN groupings g ON g.groupId = a.groupId
			JOIN customer c ON c.customerId = g.adminId
            WHERE
				a.alphanumericId = LAST_INSERT_ID();
            
			LEAVE PROC;
            
		-- update customer profile
        WHEN 14 THEN
            SET inCname = ValidString(inCname);
            SET inFname = ValidString(inFname);
            SET inLname = ValidString(inLname);
            SET inEmail = ValidString(inEmail);
            SET inAddress = ValidString(inAddress);
            SET inImg = ValidString(inImg);
            
            -- check if email or phone number belongs to another user
            SELECT phone,email INTO dPhone,dEmail FROM customer WHERE (email = inEmail OR phone = inPhone) AND customerId != inCustomerId;
			IF NOT ISNULL(dPhone) or NOT ISNULL(dEmail) THEN
				SELECT 'The customer already exists' message, dPhone phone, dEmail email, 0 updated;
				LEAVE PROC;
            END IF;
            
			UPDATE customer SET email = inEmail, phone = inPhone, cname = inCname, fname = inFname, lname = inLname, 
				cAddress = inAddress, img = if(inImg is null,img,inImg)
            WHERE
				customerId = inCustomerId;
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
		ELSE
			SELECT 'Out of actions' denoted; 
            LEAVE PROC; 
        
	END CASE;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `MESSAGE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `MESSAGE`(
	IN inAction INT,
	IN inMessageId INT,
	IN inPGroupId INT, 
	IN inGroupId INT, 
    IN inCustomerId INT,
	IN inAdminId INT,
    IN inPhone BIGINT,
	IN inReference VARCHAR(100),
	IN inTitle VARCHAR(250),
	IN inContent TEXT,
    IN inAlphanumeric VARCHAR(50),
    IN inAlphanumericId INT,
    IN inShortcodeId INT,
    IN inOffercodeId INT,
    IN inTypeId TINYINT,
    IN inMode TINYINT,
    IN inStatusId TINYINT,
    IN inUnits INT,
    IN inDelivered TINYINT,
    IN inSent TINYINT, 
    IN inTotalRecepients INT,
    IN inTotalFailed INT,
    IN inTotalDelivered INT,
	IN inSuccMessage VARCHAR(250),
	IN inErrMessage VARCHAR(250),
    IN inBlocked TINYINT,
    IN inScheduleAt DATETIME,
    IN inSentAt DATETIME,
    IN inStartDate DATETIME,
    IN inEndDate DATETIME,
    IN inStart INT,
    IN inLimit INT
)
PROC: BEGIN   

	DECLARE dAlphaId INT DEFAULT NULL;
    
	CASE inAction           
		-- new message
        WHEN 1 THEN
            SET inReference = ValidString(inReference);
            SET inTitle = ValidString(inTitle);
            SET inContent = ValidString(inContent);
            -- select CAST(inMode AS CHAR); leave proc;
            INSERT INTO messages(title,content,typeId,statusId,custom,adminId,customerId,groupId,reference,alphanumericId,units,totalRecipients,scheduled_time)
            VALUE(inTitle,inContent,inTypeId,inStatusId,CAST(inMode AS CHAR),inAdminId,inCustomerId,inPGroupId,inReference,inAlphanumericId,inUnits,inTotalRecepients,inScheduleAt);
            
            SELECT LAST_INSERT_ID() messageId, row_count() created;            
            
            LEAVE PROC;
            
        -- list of message
        WHEN 2 THEN
			SELECT *
            FROM messages
            WHERE
				groupId = inPGroupId;
			LEAVE PROC;
            
		-- message recepient
        WHEN 3 THEN
			INSERT INTO messagesRecipients(messageId,phone,statusId,sentAt,successMessage,errorMessage)
            VALUE(inMessageId,inPhone,inStatusId,inSentAt,inSuccMessage,inErrMessage);
            SELECT LAST_INSERT_ID() recepientId, ROW_COUNT() created;
			LEAVE PROC;
            
		-- update messages
        WHEN 4 THEN
			-- select inStatusId,inMessageId,inPGroupId; leave proc;
			UPDATE messages SET statusId = inStatusId 
            WHERE 
				messageId = inMessageId
                AND groupId = inPGroupId;
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
		-- update message recipients
        WHEN 5 THEN
            -- SET inSuccMessage = if(inSuccMessage='0' OR inSuccMessage='' OR inSuccMessage='null',null,inSuccMessage);
            -- SET inErrMessage = if(inErrMessage='0' OR inErrMessage='' OR inErrMessage='null',null,inErrMessage);
            
            UPDATE messagesRecipients
				SET statusId = inStatusId, deliveredAt = inSentAt, blocked = CAST(inBlocked AS CHAR)
                -- successMessage = inSuccMessage, errorMessage = inErrMessage,
            WHERE
				messageId = inMessageId
                AND phone = inPhone;
			
			SELECT ROW_COUNT() updated, mr.recipientId, m.messageId, m.groupId, mr.phone, ms.statusId, ms.status, a.alphanumericId, a.title alphanumeric, mr.blocked, mr.created, mr.deliveredAt delivered
			FROM messagesRecipients mr
			JOIN messages m ON m.messageId = mr.messageId
			JOIN alphanumeric a ON a.alphanumericId = m.alphanumericId
			JOIN messagestatus ms ON ms.statusId = mr.statusId
			WHERE
				m.messageId = inMessageId
				AND mr.phone = inPhone;
				-- AND m.groupId = inGroupId;*/
			LEAVE PROC;
            
		-- save draft
        WHEN 6 THEN
            SET inReference = ValidString(inReference);
            SET inTitle = ValidString(inTitle);
            SET inContent = ValidString(inContent);
            
            INSERT INTO messages(title,content,statusId,custom,adminId,customerId,groupId,alphanumericId,scheduled_time)
            VALUE(inTitle,inContent,inStatusId,CAST(inMode AS CHAR),inAdminId,inCustomerId,inPGroupId,inAlphanumericId,inScheduleAt);
            SET inMessageId = LAST_INSERT_ID();
            
            INSERT INTO messagesRecipientGroup(messageId,groupId) VALUE(inMessageId,inPGroupId);
            
            SELECT inMessageId messageId, row_count() created;
			LEAVE PROC;
            
		-- delete draft message
        WHEN 7 THEN -- select inMessageId,inPGroupId; leave proc;
			/*DELETE m, mg 
			FROM messages m
            JOIN messagesRecipientGroup mg ON m.messageId = mg.messageId AND m.groupId = mg.groupId
			WHERE m.messageId = inMessageId AND m.groupId = inPGroupId;*/
            DELETE FROM messagesRecipientGroup WHERE messageId = inMessageId and groupId = inPGroupId;
			DELETE FROM messages WHERE messageId = inMessageId and groupId = inPGroupId;
            SELECT ROW_COUNT() deleted;
			LEAVE PROC;
            
		-- select scheduled messages
        WHEN 8 THEN
			SELECT m.messageId, m.title, m.content, m.scheduled_time, m.groupId pgroupId, m.typeId, m.custom, m.totalRecipients,
				m.customerId, a.alphanumericId, a.title alphanumeric, m.units, m.statusId, m.scheduled_time
			FROM messages m
			JOIN alphanumeric a ON a.alphanumericId = m.alphanumericId
			WHERE
				m.statusId = 2
				AND m.scheduled_time BETWEEN NOW() - INTERVAL 3 MINUTE 
				AND NOW() + INTERVAL 1 MINUTE
			ORDER BY m.scheduled_time ASC
            LIMIT 100;
			LEAVE PROC;
            
		-- subscriber
        WHEN 9 THEN
			INSERT INTO offercodesubscriber(subscriber,offercodeId,shortcodeId,userId,groupId)
            VALUE(inPhone,inOffercodeId,inShortcodeId,inAdminId,inPGroupId);
			LEAVE PROC;

		ELSE
			SELECT 'Out of actions' message;
            LEAVE PROC; 
        
	END CASE;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `PAYMENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `PAYMENT`(
	IN inAction INT,
	IN inpaymentId INT, -- tranId
	IN inGroupId INT, 
    IN inCustomerId INT, 
    IN inMessageId INT,
	IN inAdminId INT, 
	IN inPhone BIGINT,
	IN inAmount DOUBLE,
	IN inRate DOUBLE,
    IN inUnits INT,
	IN inFname VARCHAR(250),
	IN inMname VARCHAR(250),
	IN inLname VARCHAR(250),
	IN inReference VARCHAR(100),
	IN inMerchantReqId VARCHAR(100),
	IN inCheckoutReqId VARCHAR(100),
    IN inModeId INT,
    IN inStatusId INT,
    IN inPosted TINYINT,
	IN inDescrp VARCHAR(1000),
    IN inThirdpartyDate DATETIME,
    IN inStartDate DATETIME,
    IN inEndDate DATETIME,
    IN inStart INT, 
    IN inLimit INT
)
PROC: BEGIN   

	/*DECLARE dPhone,dEmail,inPhone VARCHAR(255) DEFAULT NULL;
    
	DECLARE dPCode,dUserId,dSaccoId INT DEFAULT NULL;
    DECLARE dECode VARCHAR(255) DEFAULT NULL; 
    DECLARE dTime DATETIME DEFAULT NULL; */
    
    DECLARE dTranId, dOpeningbalance, dClosingbalance, dUnits INT DEFAULT 0;
    
    DECLARE dRollBack BOOL DEFAULT 0;
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET dRollBack = 1;
    
	CASE inAction            
		-- new payment
        WHEN 1 THEN
            SET inFname = if(inFname='0' OR inFname='' OR inFname='null',null,inFname);
            SET inMname = if(inMname='0' OR inMname='' OR inMname='null',null,inMname);
            SET inLname = if(inLname='0' OR inLname='' OR inLname='null',null,inLname);
            SET inReference = if(inReference='0' OR inReference='' OR inReference='null',null,inReference);
            SET inMerchantReqId = if(inMerchantReqId='0' OR inMerchantReqId='' OR inMerchantReqId='null',null,inMerchantReqId);
            SET inCheckoutReqId = if(inCheckoutReqId='0' OR inCheckoutReqId='' OR inCheckoutReqId='null',null,inCheckoutReqId);
            SET inDescrp = if(inDescrp='0' OR inDescrp='' OR inDescrp='null',null,inDescrp);
            
            -- select inFname,inMname,inLname,inModeId,inStatusId,inAdminId,inCustomerId,inGroupId,inAmount,1,inPhone; leave proc;
            
            -- START TRANSACTION;
				INSERT INTO payments(fname,mname,lname,modeId,statusId,customerId,groupId,amount,units,phone)
                VALUE(inFname,inMname,inLname,inModeId,inStatusId,inCustomerId,inGroupId,inAmount,(inAmount/CalculatePrice(inAmount)),inPhone);
				-- SET inpaymentId = LAST_INSERT_ID();
                SELECT LAST_INSERT_ID() id, row_count() created;
			
			/*IF dRollBack THEN
				ROLLBACK;
				SELECT 'roll back' message, 0 created;
			ELSE 
				COMMIT;	
				SELECT 1 created, inpaymentId paymentId;
			END IF;*/
            LEAVE PROC;                
			
        -- update from request callback
        WHEN 2 THEN
            SET inMerchantReqId = if(inMerchantReqId='0' OR inMerchantReqId='' OR inMerchantReqId='null',null,inMerchantReqId);
            SET inCheckoutReqId = if(inCheckoutReqId='0' OR inCheckoutReqId='' OR inCheckoutReqId='null',null,inCheckoutReqId);
            SET inDescrp = if(inDescrp='0' OR inDescrp='' OR inDescrp='null',null,inDescrp);
			
            UPDATE payments SET referenceI = inMerchantReqId, referenceII = inCheckoutReqId, ResponseDescription = inDescrp, statusId = inStatusId
            WHERE
				paymentId = inpaymentId;
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
		
        -- update from request callback
        WHEN 3 THEN
			SET inReference = if(inReference='0' OR inReference='' OR inReference='null',null,inReference);
            SET inMerchantReqId = if(inMerchantReqId='0' OR inMerchantReqId='' OR inMerchantReqId='null',null,inMerchantReqId);
            SET inCheckoutReqId = if(inCheckoutReqId='0' OR inCheckoutReqId='' OR inCheckoutReqId='null',null,inCheckoutReqId);
            SET inDescrp = if(inDescrp='0' OR inDescrp='' OR inDescrp='null',null,inDescrp);
			-- select inReference,inMerchantReqId,inCheckoutReqId,inDescrp,inThirdpartyDate; leave proc;
            UPDATE payments SET 
				reference = inReference, ResponseDescription = inDescrp, -- statusId = inStatusId, posted = '1', 
                posteddate = NOW(), thirdpartyTime = inThirdpartyDate
            WHERE
				referenceI = inMerchantReqId
                AND referenceII = inCheckoutReqId
                AND ResponseDescription != inDescrp;
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
        -- update from registered callback
        WHEN 4 THEN
			START TRANSACTION;
				SELECT paymentId,groupId INTO inpaymentId,inGroupId
                FROM payments 
                WHERE
					paymentId = inpaymentId
					-- AND phone = inPhone
                    AND statusId = 2
                    AND posted = '0';
                    
				IF inGroupId IS NULL THEN
					SELECT 'The payment is posted or doesnt exist' message, 0 created;
					LEAVE PROC;
                END IF;
                
				SELECT closingbalance INTO dClosingbalance
				FROM groupunitbalance 
				WHERE 
					groupId = inGroupId
                    AND active = '1'
				ORDER BY tranId DESC
				LIMIT 1;
                    
				UPDATE payments SET
					fname = inFname, reference = inReference, -- mname = inMname, lname = inLname,
                    statusId = inStatusId, posted = '1', posteddate = NOW(), thirdpartyTime = inThirdpartyDate
				WHERE 
					paymentId = inpaymentId
					-- AND phone = inPhone
                    AND statusId = 2
                    AND posted = '0';
                
                SET dUnits = (inAmount/CalculatePrice(inAmount)); 
                    
				INSERT INTO groupunitbalance(groupId,paymentId,openingbalance,increase,closingbalance)
				VALUE(inGroupId,inpaymentId,dClosingbalance,dUnits,(dClosingbalance+dUnits));
                    
			IF dRollBack THEN
				ROLLBACK;
				SELECT 'Technical problem' message, 0 updated; -- , inGroupId,inpaymentId,dClosingbalance,dUnits,(dClosingbalance+dUnits);
			ELSE 
				COMMIT;	
				SELECT 1 updated; -- , inGroupId,inpaymentId,dClosingbalance,dUnits,(dClosingbalance+dUnits);
			END IF;
                    
			-- SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
		-- list of payment per group
        WHEN 5 THEN
			SET inReference = ValidString(inReference);
            
			SELECT p.paymentId, p.fname, p.mname, p.lname, u.userId adminId, u.fname afname, u.lname alname, s.statusId, s.status, p.units, 
				p.amount, pm.modeId, pm.mode, p.phone, p.reference, p.created
			FROM payments p
			JOIN paymentstatus s ON s.statusId = p.statusId
            JOIN paymentmode pm ON pm.modeId = p.modeId
            LEFT JOIN users u ON u.userId = p.adminId
			WHERE
				if(inGroupId, p.groupId = inGroupId,1)
				AND if(inpaymentId, p.paymentId = inpaymentId,1)
                AND if(inAmount, p.amount = inAmount,1)
				AND if(inStatusId, p.statusId = inStatusId,1)
				AND if(inModeId, p.modeId = inModeId,1)
                AND if(inStartDate, p.created >= inStartDate,1)
                AND if(inEndDate, p.created <= inEndDate,1) 
                AND if(inReference, p.reference = inReference,1)
			ORDER BY p.created DESC
			LIMIT inStart,inLimit;			
			LEAVE PROC;
            
		-- get account balance
        WHEN 6 THEN
			/*
            SELECT closingbalance balance
			FROM groupunitbalance
			WHERE
				groupId = inGroupId
				AND active = '1'
			ORDER BY created DESC
			LIMIT 1;
            */
            SELECT units as balance FROM groupings WHERE groupId = inGroupId LIMIT 1;
			LEAVE PROC;
            
		-- get account balance and reserve units that will be deducted after sms have been processed
        WHEN 7 THEN
			START TRANSACTION;
				SELECT closingbalance INTO dClosingbalance
				FROM groupunitbalance
				WHERE
					groupId = inGroupId
					AND active = '1'
				ORDER BY created DESC
				LIMIT 1;
				-- select inGroupId,dClosingbalance,inUnits,(dClosingbalance-dUnits); leave proc;
				IF dClosingbalance >= inUnits THEN
					INSERT INTO groupunitbalance(groupId,openingbalance,decrease,closingbalance)
					VALUE(inGroupId,dClosingbalance,inUnits,(dClosingbalance-inUnits)); 
                    SET dTranId = LAST_INSERT_ID();
				END IF;
                
			IF dRollBack THEN
				ROLLBACK;
				SELECT 'Technical problem' message, 0 created;
			ELSE 
				COMMIT;	
				SELECT 1 updated, dClosingbalance balance, dTranId transactionId;
			END IF;   
			LEAVE PROC;
            
		-- manual top-up
        WHEN 8 THEN
			-- insert into tpsys.groupunitbalance(groupId,openingbalance,increase,closingbalance,reference)
			-- value(1,0,100000,100000,'JM001');        
			LEAVE PROC;
            
		-- update units balance
        WHEN 9 THEN -- select inMessageId,inpaymentId,inGroupId; leave proc;
			UPDATE groupunitbalance SET 
				messageId = inMessageId 
			WHERE
				tranId = inpaymentId
                AND groupId = inGroupId;
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
        -- update unsuccessful callback request 
        WHEN 10 THEN
            SET inMerchantReqId = if(inMerchantReqId='0' OR inMerchantReqId='' OR inMerchantReqId='null',null,inMerchantReqId);
            SET inCheckoutReqId = if(inCheckoutReqId='0' OR inCheckoutReqId='' OR inCheckoutReqId='null',null,inCheckoutReqId);
            SET inDescrp = if(inDescrp='0' OR inDescrp='' OR inDescrp='null',null,inDescrp);
			
            UPDATE payments SET 
				ResponseDescription = inDescrp, statusId = inStatusId, thirdpartyTime = inThirdpartyDate
            WHERE
				referenceI = inMerchantReqId
                AND referenceII = inCheckoutReqId
                AND ResponseDescription != inDescrp
                AND posted = '0';
			SELECT ROW_COUNT() updated;
			LEAVE PROC;   
            
		-- pricing
        WHEN 11 THEN
			SELECT priceId, pFrom, pTo, price, created
			FROM pricing;
			LEAVE PROC;
            
		-- admin manually replenishing
        WHEN 12 THEN
            -- SET inReference = if(inReference='0' OR inReference='' OR inReference='null',null,inReference);
            SET inReference = ValidString(inReference);
            
			IF inRate THEN
				SET dUnits = inAmount / inRate;
			ELSE
				SET dUnits = inAmount / CalculatePrice(inAmount);
			END IF;

            START TRANSACTION;
				INSERT INTO payments(modeId,statusId,adminId,customerId,groupId,amount,units,reference)
				VALUE(inModeId,inStatusId,inAdminId,inCustomerId,inGroupId,inAmount,dUnits,inReference);
                
                SET inpaymentId = LAST_INSERT_ID();
                
				SELECT closingbalance INTO dClosingbalance
				FROM groupunitbalance 
				WHERE 
					groupId = inGroupId
                    AND active = '1'
				ORDER BY tranId DESC
				LIMIT 1;
                    
				INSERT INTO groupunitbalance(groupId,paymentId,openingbalance,increase,closingbalance)
				VALUE(inGroupId,inpaymentId,dClosingbalance,dUnits,(dClosingbalance+dUnits));             
                
			IF dRollBack THEN
				ROLLBACK;
				SELECT 'Technical problem' message, 0 created; -- , inGroupId,inpaymentId,dClosingbalance,dUnits,(dClosingbalance+dUnits);
			ELSE 
				COMMIT;
				SELECT 1 created, inpaymentId id, dUnits units;
			END IF;
			LEAVE PROC;                
			
			-- SELECT LAST_INSERT_ID() id, row_count() created;        
			-- LEAVE PROC;
            
		-- Total payments per group
		WHEN 13 THEN
			SELECT SUM(amount) amount
			FROM payments
			WHERE
				groupId = inGroupId
				AND statusId = inStatusId;
			LEAVE PROC;
            
		-- stats
        WHEN 14 THEN
			SELECT 
			  DATE_FORMAT(created, '%Y-%m') month,
			  SUM(amount) amount
			FROM payments
			WHERE 
				groupId = inGroupId
				AND created >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
			GROUP BY month
            ORDER BY month;
			LEAVE PROC;
            
		ELSE 
			SELECT 'Out of actions' denoted; 
            LEAVE PROC; 
        
	END CASE;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `TEMPLATE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `TEMPLATE`(
	IN inAction INT,
	IN inTemplateId INT,
	IN inGroupId INT,
    IN inCustomerId INT,
	IN inAdminId INT,
	IN inTitle VARCHAR(250),
	IN inMessage TEXT,
    IN inActiveF VARCHAR(5),
    IN inStartDate DATETIME,
    IN inEndDate DATETIME,
    IN inStart INT,
    IN inLimit INT
)
PROC: BEGIN   
    
	CASE inAction            
		-- new template
        WHEN 1 THEN
            SET inTitle = ValidString(inTitle);
            SET inMessage = ValidString(inMessage);
            /*
            IF(select 1 from template where (title=inTitle and groupId=inGroupId) or (template=inMessage and groupId=inGroupId) ) THEN
				SELECT 
					templateId,
					CASE
						WHEN title = inTitle THEN 'Duplicate title in this group'
						WHEN template = inMessage THEN 'Duplicate template in this group'
					END AS reason
				FROM template
				WHERE groupId = inGroupId
				  AND (
						title = inTitle
					 OR template = inMessage
				  )
				LIMIT 1;
                leave proc;
			END IF;
            
            INSERT INTO template(title,template,customerId,groupId)
            VALUE(inTitle,inMessage,inCustomerId,inGroupId);
            
            SELECT last_insert_id() id, row_count() created;
            */
			/*INSERT INTO template (title, template, customerId, groupId)
			SELECT inTitle, inMessage, inCustomerId, inGroupId
			WHERE NOT EXISTS (
				SELECT 1
                FROM template
				WHERE 
					groupId = inGroupId
                    AND (title = inTitle or template = inMessage)
			);*/
            
			IF NOT EXISTS (
				SELECT 1 FROM template WHERE (title = inTitle or template = inMessage) AND groupId = inGroupId
			)
			THEN
				INSERT INTO template(title, template, customerId, groupId)
				VALUES (inTitle, inMessage, inCustomerId, inGroupId);
                SELECT LAST_INSERT_ID() id, row_count() created;
			ELSE
				-- SIGNAL SQLSTATE '45000'
				-- SET MESSAGE_TEXT = 'A template with the same title already exists in this group.';
                select 'The template already exists' message, 0 created;
			END IF;
            
            LEAVE PROC;                
			
        -- list of templates
        WHEN 2 THEN
			SELECT templateId, title, template, created
            FROM template 
            WHERE
				groupId = inGroupId;
			LEAVE PROC;
            
		-- update template
        WHEN 3 THEN
			UPDATE template SET title = inTitle, template = inMessage, customerId = inCustomerId -- , active = inActiveF
			WHERE
				groupId = inGroupId
				AND templateId = inTemplateId; 
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
		-- delete template
        WHEN 4 THEN -- select inTemplateId, inGroupId; leave proc;
			DELETE FROM template WHERE templateId = inTemplateId AND groupId = inGroupId;
            SELECT ROW_COUNT() deleted;
			LEAVE PROC;
            
		ELSE 
			SELECT 'Out of actions' denoted; 
            LEAVE PROC; 
        
	END CASE;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USERS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `USERS`(
	IN inAction INT, 
	IN inUserId INT, 
	IN inEmail VARCHAR(100),
	IN inPhone VARCHAR(100),
	IN inFname VARCHAR(100),
    IN inLname VARCHAR(255),
    IN inActiveId TINYINT,
    IN inResetPass TINYINT, 
	IN inPass VARCHAR(255),
	IN inCode INT,
    IN inTypeId INT,
	IN inRoleId INT,
	IN inAdminId INT,
    IN inPassExpire DATETIME,
    IN inStartDate DATETIME,
    IN inEndDate DATETIME,
    IN inStart INT, 
    IN inLimit INT 
)
PROC: BEGIN

	DECLARE dPhone,dEmail,dFname VARCHAR(255) DEFAULT NULL;
	DECLARE dCode,dUserId INT DEFAULT NULL;
    -- DECLARE dTime DATETIME DEFAULT NULL;
    
    DECLARE dRollBack BOOL DEFAULT 0;
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION SET dRollBack = 1;     

	CASE inAction
		-- create customer		
		/*WHEN 1 THEN
            SET inEmail = if(inEmail='0' OR inEmail='' OR inEmail='null',null,inEmail);
            SET inPhone = if(inPhone='0' OR inPhone='' OR inPhone='null',null,inPhone);
            
            SELECT customerId INTO dCustomerId FROM customer WHERE phone = inPhone or email = inEmail;
            
			IF NOT ISNULL(dCustomerId) THEN
				SELECT email INTO dEmail FROM customer WHERE email = inEmail;
				SELECT phone INTO dPhone FROM customer WHERE phone = inPhone;
				SELECT 'The customer already exists' message, dPhone phone, dEmail email, 0 created, dCustomerId customerId;
				LEAVE PROC;
            END IF;
            
            SET inCname = if(inCname='0' OR inCname='' OR inCname='null',null,inCname);
            SET inFname = if(inFname='0' OR inFname='' OR inFname='null',null,inFname);
            SET inLname = if(inLname='0' OR inLname='' OR inLname='null',null,inLname);
            SET inPass = if(inPass='0' OR inPass='' OR inPass='null',null,inPass);
            SET inPcode = if(inPcode='0' OR inPcode='' OR inPcode='null',null,inPcode);
            SET inEcode = if(inEcode='0' OR inEcode='' OR inEcode='null',null,inEcode);
            -- SET inVType = if(inVType='0' OR inVType='' OR inVType='null',null,inVType);
            -- SET inRoleIds = if(inRoleIds='0' OR inRoleIds='' OR inRoleIds='null',null,inRoleIds);
            
            START TRANSACTION;
				INSERT INTO customer(email,phone,cname,fname,lname,pass,pCode,eCode,customerTypeId)
				VALUE(inEmail,inPhone,inCname,inFname,inLname,inPass,inPcode,inEcode,inTypeId);
				-- select inEmail,inPhone,inCname,inFname,inLname,inPass,inPcode,inEcode,inTypeId;
				SET dCustomerId = LAST_INSERT_ID();
				
				-- grouping
				INSERT INTO groupings(adminId,active)
				VALUE(dCustomerId,'1');
				SET dGroupId = LAST_INSERT_ID();
				
				-- participants
				INSERT INTO participant(groupId,customerId,active)
				VALUE(dGroupId,dCustomerId,'1');
                
                -- roles
                INSERT INTO customersroles(customerId,groupId,roleId)
                VALUE(dCustomerId,dGroupId,inRoleId);
                -- INSERT INTO customersroles(customerId,groupId,roleId)
                -- VALUE(dCustomerId,dGroupId,inRoleId);                
				
				-- SELECT dCustomerId id, dGroupId groupId, ROW_COUNT() created;
                
			IF dRollBack THEN
				ROLLBACK;
				SELECT 'There is a technical problem' message, 0 created;
			ELSE 
				COMMIT;	
				-- SELECT dCustomerId id, dGroupId groupId, inRoleId roleId, 'admin' role, 1 created;
				SELECT roleId, title, dCustomerId id, dGroupId groupId, 1 created
				FROM roles
				WHERE roleId = inRoleId;                  
			END IF;            
            LEAVE PROC;
        
        -- verify customer
		WHEN 2 THEN
            SET inEmail = if(inEmail='0' OR inEmail='' OR inEmail='null',null,inEmail);
            SET inPhone = if(inPhone='0' OR inPhone='' OR inPhone='null',null,inPhone);
            SET inPcode = if(inPcode='0' OR inPcode='' OR inPcode='null',null,inPcode);
            SET inEcode = if(inEcode='0' OR inEcode='' OR inEcode='null',null,inEcode);
            SET inVType = if(inVType='0' OR inVType='' OR inVType='null',null,inVType); -- select inVType, inPcode, inEcode; leave proc;
            
			UPDATE customer SET pCode = null, eCode = null, verificationTime = now(), verificationType = inVType, verified = 1
            WHERE
				verified = 0 
                AND pCode = inPcode
                AND (phone = inPhone or email = inEmail);
                -- AND if( inPcode, (pCode=inPcode and phone=inPhone), 1)
                -- AND if(inEcode, (eCode=inEcode and email=inEmail), 1);
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
		*/
		-- update customer forgotten password
        WHEN 3 THEN
            -- SET inCode = ValidString(inCode);
			UPDATE users SET passreset = 1, vcode = inCode
            WHERE
				userId = inUserId;
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
		-- update user password
        WHEN 4 THEN
            -- SET inCode = ValidString(inCode);      
			UPDATE users SET passreset = null, vcode = null, pass = inPass
            WHERE
				userId = inUserId
                AND passreset = 1
                AND vcode = inCode;
			SELECT ROW_COUNT() updated;
			LEAVE PROC;
            
		-- list of roles and permissions
		WHEN 5 THEN
			SELECT r.roleId, r.title, p.permissionId, p.permission, g.groupId, g.icon, r.created --  g.title gtitle,
			FROM permissions p
			JOIN permsgroup g ON g.groupId = p.groupId
			JOIN rolepermission rp ON rp.permissionId = p.permissionId
			JOIN roles r on r.roleId = rp.roleId;
			LEAVE PROC;
            
		-- create new customer under certain group
		/* WHEN 5 THEN
            SET inEmail = if(inEmail='0' OR inEmail='' OR inEmail='null',null,inEmail);
            SET inPhone = if(inPhone='0' OR inPhone='' OR inPhone='null',null,inPhone);
            
			SELECT c.customerId INTO dCustomerId 
			FROM customer c
			JOIN participant p ON p.customerId = c.customerId
			WHERE
				p.groupId = inGroupId
				AND (c.email = inEmail OR c.phone = inPhone);
            
			IF NOT ISNULL(dCustomerId) THEN
				SELECT email INTO dEmail FROM customer WHERE email = inEmail;
				SELECT phone INTO dPhone FROM customer WHERE phone = inPhone;
				SELECT 'The customer already exists' message, dCustomerId, inGroupId, dPhone phone, dEmail email, 0 created, dCustomerId customerId;
				LEAVE PROC;
            END IF;
            
            -- check if the customer exists at all
            IF((select count(1) from customer where phone = inPhone or email = inEmail) > 1) THEN
				SELECT 'Contact the customer for clear details' message, 0 created;
			ELSE
				SELECT customerId INTO dCustomerId FROM customer WHERE phone = inPhone or email = inEmail;
            END IF;

            -- SET inCname = if(inCname='0' OR inCname='' OR inCname='null',null,inCname);
            SET inFname = ValidString(inFname);
            SET inLname = ValidString(inLname);
            SET inPass = ValidString(inPass);
            -- SET inPcode = if(inPcode='0' OR inPcode='' OR inPcode='null',null,inPcode);
            -- SET inEcode = if(inEcode='0' OR inEcode='' OR inEcode='null',null,inEcode);
            -- SET inVType = if(inVType='0' OR inVType='' OR inVType='null',null,inVType);
            -- SET inRoleIds = if(inRoleIds='0' OR inRoleIds='' OR inRoleIds='null',null,inRoleIds);
            
            START TRANSACTION;
				IF dCustomerId IS NULL THEN
					INSERT INTO customer(email,phone,fname,lname,pass)
					VALUE(inEmail,inPhone,inFname,inLname,inPass);
					SET dCustomerId = LAST_INSERT_ID(), dNew = 1;
				END IF;
				
				-- participants
				INSERT INTO participant(groupId,customerId)
				VALUE(inGroupId,dCustomerId);
                
                -- roles
                INSERT INTO customersroles(customerId,groupId,roleId,adminId)
                VALUE(dCustomerId,inGroupId,inRoleId,inCustomerId);
                
			IF dRollBack THEN
				ROLLBACK;
				SELECT 'There is a technical problem' message, 0 created;
			ELSE
				COMMIT;	
				-- SELECT dCustomerId id, inGroupId groupId, 1 created, dNew newly;
				SELECT roleId, title, dCustomerId id, inGroupId groupId, 1 created, dNew newly
				FROM roles
				WHERE roleId = inRoleId;               
			END IF;
            LEAVE PROC;
            
		-- customers groupings 
        WHEN 6 THEN
            -- SELECT groupId FROM participant WHERE customerId = inCustomerId;
			SELECT r.roleId, r.title role, cr.groupId, cr.active
			FROM customersroles cr
			JOIN roles r ON r.roleId = cr.roleId
			WHERE customerId = inCustomerId;            
			LEAVE PROC;
            
		-- customer role per group
        WHEN 7 THEN
			SELECT r.roleId, r.title
			FROM customersroles cr
			JOIN roles r ON r.roleId = cr.roleId
			WHERE 
				customerId = inCustomerId 
				AND groupId = inGroupId;        
			LEAVE PROC;
            
		-- group accounts
        WHEN 8 THEN
			SELECT g.groupId, g.active, c.customerId, c.fname, c.lname, c.email, c.phone, g.created
			FROM groupings g
			JOIN customer c ON c.customerId = g.adminId;
			LEAVE PROC;
            
		-- customers
		WHEN 9 THEN
			SELECT c.email, c.phone, c.cname, c.fname, c.lname, c.active, c.passreset, c.pass, ct.typeId, ct.type, c.created
			FROM customer c
			JOIN customertype ct ON ct.typeId = c.customertypeId
            LIMIT inStart,inLimit;
			LEAVE PROC;
            
		-- alphanumeric list
        WHEN 10 THEN
			SET inAlphanumeric = validString(inAlphanumeric);
            
			SELECT a.alphanumericId _id, a.title, a.active, g.groupId, c.customerId, c.cname, c.fname, c.lname, c.active, ct.type, g.units, a.created
			FROM alphanumeric a
			JOIN groupings g ON g.groupId = a.groupId
			JOIN customer c ON c.customerId = g.adminId
			JOIN customertype ct ON ct.typeId = c.customertypeId
            WHERE
				if( inAlphanumericId, a.alphanumericId = inAlphanumericId, 1)
                AND if(inAlphanumeric, a.title = inAlphanumeric, 1)          
            ORDER BY a.created DESC
            LIMIT inStart,inLimit;
			LEAVE PROC;
		*/
            
		-- suspend customer
        /*WHEN 12 THEN
			IF NOT EXISTS(SELECT 1 FROM participant WHERE groupId = inGroupId AND customerId = inCustomerId) THEN
				SIGNAL SQLSTATE '45000';
				SELECT 'The customer does not exist' message, 0 updated, MYSQL_ERRNO = 1001;
                LEAVE PROC;
			END IF;
            
            UPDATE customer SET active = '0' WHERE customerId = inCustomerId;
            UPDATE participant SET active = '0' WHERE groupId = inGroupId AND customerId = inCustomerId;
            SELECT ROW_COUNT() suspended;
			LEAVE PROC;
            
		-- create alphanumeric under account
        WHEN 13 THEN
			SET inAlphanumeric = ValidString(inAlphanumeric);
            
			IF EXISTS(select 1 from alphanumeric where title = inAlphanumeric ) THEN
				SELECT 'The alphanumeric already exists' message, 0 created;
				LEAVE PROC;
            END IF;
            
			INSERT INTO alphanumeric(title,userId,customerId,groupId)
            VALUE(inAlphanumeric,inAdminId,inCustomerId,inGroupId);
            
			SELECT a.alphanumericId, a.title, c.cname, c.fname, c.lname, g.groupId, c.customerId, a.active, a.created
			FROM alphanumeric a
			JOIN groupings g ON g.groupId = a.groupId
			JOIN customer c ON c.customerId = g.adminId
            WHERE
				a.alphanumericId = LAST_INSERT_ID();
            
			LEAVE PROC;
		*/
		ELSE
			SELECT 'Out of actions' denoted; 
            LEAVE PROC; 
        
	END CASE;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-16 22:34:50
