-- 
-- XAMS Database update from 0.0.18 to 0.0.19 
-- 

ALTER TABLE `pm_users` ADD `SpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamCheckOut` ,
ADD `HighSpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamScore` ;

ALTER TABLE `pm_preferences` ADD `Admin` varchar(50) NOT NULL AFTER `LogLevel`;
ALTER TABLE `pm_preferences` ADD `SpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `LoginWelcome`,
ADD `HighSpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamScore` ;

ALTER TABLE `pm_sites` ADD `SpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamCheckOut` ,
ADD `HighSpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamScore` ;

ALTER TABLE `pm_resellers` ADD `SpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamCheckOut` ,
ADD `HighSpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamScore` ;

ALTER TABLE `pm_site_templates` ADD `SpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamCheckOut` ,
ADD `HighSpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamScore` ;

ALTER TABLE `pm_user_templates` ADD `SpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamCheckOut` ,
ADD `HighSpamScore` DECIMAL( 10, 0 ) NOT NULL AFTER `SpamScore` ;
--
