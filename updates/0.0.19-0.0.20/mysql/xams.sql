-- 
-- XAMS Database update from 0.0.19 to 0.0.20
-- 

ALTER TABLE `pm_users` ADD `UsedQuota` INT( 11 ) NOT NULL AFTER `Quota` ; 

--
