-- New tables


--
-- Table structure for table 'pm_exim_filters'
--

CREATE TABLE pm_exim_filters (
  UserID int(11) NOT NULL default '0',
  Filter text,
  Active enum('false','true') NOT NULL default 'true',
  Added datetime default NULL,
  Updated timestamp(14) NOT NULL,
  PRIMARY KEY  (UserID),
  KEY Active (Active)
) TYPE=MyISAM;

-- General structure updates
-- This has to be done in: pm_aliases, pm_customer_info_fields, pm_site_templates


--
-- Table structure update for table 'pm_aliases'
--

ALTER TABLE `pm_aliases` ADD `BounceForward` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `RightPart`;

--
-- Table structure update for table 'pm_customer_info_fields'
--

ALTER TABLE `pm_customer_info_fields` ADD `ACL_Customer` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `ACL_Reseller`;

--
-- Table structure update for table 'pm_domains'
--

ALTER TABLE `pm_domains` DROP INDEX `Name`, DROP PRIMARY KEY, ADD UNIQUE (`Name`), ADD `ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

--
-- Table structure update for table 'pm_preferences'
--

ALTER TABLE `pm_preferences` ADD `LastNewsCheck` DATETIME NOT NULL AFTER `LastVersionCheck`;

--
-- Table structure update for table 'pm_reseller_info_fields'
--

ALTER TABLE `pm_reseller_info_fields` ADD `ACL_Reseller` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `LdapName`;

--
-- Table structure update for table 'pm_site_templates'
--

ALTER TABLE `pm_site_templates` DROP `CustomerID`;
ALTER TABLE `pm_site_templates` ADD `BounceForward1` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `RightPart1`;
ALTER TABLE `pm_site_templates` ADD `BounceForward2` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `RightPart2`;
ALTER TABLE `pm_site_templates` ADD `BounceForward3` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `RightPart3`;
ALTER TABLE `pm_site_templates` ADD `BounceForward4` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `RightPart4`;
ALTER TABLE `pm_site_templates` ADD `BounceForward5` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `RightPart5`;
ALTER TABLE `pm_site_templates`
    ADD `VirusCheckIn` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `AddrType`,
    ADD `VirusCheckOut` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `VirusCheckIn`;

--
-- Table structure update for table 'pm_sites'
--

ALTER TABLE `pm_sites` ADD `SiteState2` ENUM('default','locked','lockedbounce') DEFAULT 'default' NOT NULL AFTER `SiteState`;
UPDATE `pm_sites` SET `SiteState2`='lockedbounce' WHERE `SiteState`='locked_bounce';
ALTER TABLE `pm_sites` DROP `SiteState`;
ALTER TABLE `pm_sites` CHANGE `SiteState2` `SiteState` ENUM('default','locked','lockedbounce') DEFAULT 'default' NOT NULL;
ALTER TABLE `pm_sites`
    ADD `VirusCheckIn` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `AddrType`,
    ADD `VirusCheckOut` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `VirusCheckIn`;

--
-- Table structure update for table 'pm_users'
--

ALTER TABLE `pm_users` ADD `AccountState2` ENUM('default','locked','lockedbounce') DEFAULT 'default' NOT NULL AFTER `AccountState`;
UPDATE `pm_users` SET `AccountState2`='lockedbounce' WHERE `AccountState`='locked_bounce';
ALTER TABLE `pm_users` DROP `AccountState`;
ALTER TABLE `pm_users` CHANGE `AccountState2` `AccountState` ENUM('default','locked','lockedbounce') DEFAULT 'default' NOT NULL;
ALTER TABLE `pm_users`
    ADD `VirusCheckIn` ENUM('false','true') AFTER `AddrType`,
    ADD `VirusCheckOut` ENUM('false','true') AFTER `VirusCheckIn`,
    ADD `AutoReply` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `RelayOnCheck`,
    ADD `AutoReplyText` TINYTEXT AFTER `AutoReply`;
ALTER TABLE `pm_users` ADD INDEX (`AutoReply`);


-- Change ENUM('n','y')-Fields to ENUM('false','true')
-- This has to be done in: pm_admins, pm_aliases, pm_customers, pm_dns, pm_preferences,
-- pm_resellers, pm_user_templates, pm_users


--
-- Table structure update for table 'pm_admins'
--

ALTER TABLE `pm_admins` ADD `Locked2` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `Locked`;
UPDATE `pm_admins` SET `Locked2`='false' WHERE `Locked`='n';
UPDATE `pm_admins` SET `Locked2`='true' WHERE `Locked`='y';
ALTER TABLE `pm_admins` DROP `Locked`;
ALTER TABLE `pm_admins` CHANGE `Locked2` `Locked` ENUM('false','true') DEFAULT 'false' NOT NULL;

--
-- Table structure update for table 'pm_customers'
--

ALTER TABLE `pm_customers` ADD `Locked2` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `Locked`;
UPDATE `pm_customers` SET `Locked2`='false' WHERE `Locked`='n';
UPDATE `pm_customers` SET `Locked2`='true' WHERE `Locked`='y';
ALTER TABLE `pm_customers` DROP `Locked`;
ALTER TABLE `pm_customers` CHANGE `Locked2` `Locked` ENUM('false','true') DEFAULT 'false' NOT NULL;

--
-- Table structure update for table 'pm_dns'
--

ALTER TABLE `pm_dns` ADD `SerialAutomatic2` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `SerialAutomatic`;
UPDATE `pm_dns` SET `SerialAutomatic2`='false' WHERE `SerialAutomatic`='n';
UPDATE `pm_dns` SET `SerialAutomatic2`='true' WHERE `SerialAutomatic`='y';
ALTER TABLE `pm_dns` DROP `SerialAutomatic`;
ALTER TABLE `pm_dns` CHANGE `SerialAutomatic2` `SerialAutomatic` ENUM('false','true') DEFAULT 'false' NOT NULL;
ALTER TABLE `pm_dns` CHANGE `ZoneType` `ZoneType` CHAR(1) DEFAULT 'm' NOT NULL;

ALTER TABLE `pm_dns` ADD `Changed2` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `Changed`;
UPDATE `pm_dns` SET `Changed2`='false' WHERE `Changed`='n';
UPDATE `pm_dns` SET `Changed2`='true' WHERE `Changed`='y';
ALTER TABLE `pm_dns` DROP `Changed`;
ALTER TABLE `pm_dns` CHANGE `Changed2` `Changed` ENUM('false','true') DEFAULT 'true' NOT NULL;

--
-- Table structure update for table 'pm_preferences'
--

ALTER TABLE `pm_preferences` ADD `NewVersionCheck2` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `NewVersionCheck`;
UPDATE `pm_preferences` SET `NewVersionCheck2`='false' WHERE `NewVersionCheck`='n';
UPDATE `pm_preferences` SET `NewVersionCheck2`='true' WHERE `NewVersionCheck`='y';
ALTER TABLE `pm_preferences` DROP `NewVersionCheck`;
ALTER TABLE `pm_preferences` CHANGE `NewVersionCheck2` `NewVersionCheck` ENUM('false','true') DEFAULT 'false' NOT NULL;

ALTER TABLE `pm_preferences` ADD `OnlineNews` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `OnlineAbout`;
UPDATE `pm_preferences` SET `OnlineNews`='false' WHERE `OnlineAbout`='n';
UPDATE `pm_preferences` SET `OnlineNews`='true' WHERE `OnlineAbout`='y';
ALTER TABLE `pm_preferences` DROP `OnlineAbout`;

--
-- Table structure update for table 'pm_resellers'
--

ALTER TABLE `pm_resellers` ADD `Locked2` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `Locked`;
UPDATE `pm_resellers` SET `Locked2`='false' WHERE `Locked`='n';
UPDATE `pm_resellers` SET `Locked2`='true' WHERE `Locked`='y';
ALTER TABLE `pm_resellers` DROP `Locked`;
ALTER TABLE `pm_resellers` CHANGE `Locked2` `Locked` ENUM('false','true') DEFAULT 'false' NOT NULL;
ALTER TABLE `pm_resellers`
    ADD `VirusCheckIn` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `MaxUserQuota`,
    ADD `VirusCheckOut` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `VirusCheckIn`;

--
-- Table structure update for table 'pm_user_templates'
--

ALTER TABLE `pm_user_templates` ADD `RelayOnAuth2` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `RelayOnAuth`;
UPDATE `pm_user_templates` SET `RelayOnAuth2`='false' WHERE `RelayOnAuth`='n';
UPDATE `pm_user_templates` SET `RelayOnAuth2`='true' WHERE `RelayOnAuth`='y';
ALTER TABLE `pm_user_templates` DROP `RelayOnAuth`;
ALTER TABLE `pm_user_templates` CHANGE `RelayOnAuth2` `RelayOnAuth` ENUM('false','true') DEFAULT 'true' NOT NULL;

ALTER TABLE `pm_user_templates` ADD `RelayOnCheck2` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `RelayOnCheck`;
UPDATE `pm_user_templates` SET `RelayOnCheck2`='false' WHERE `RelayOnCheck`='n';
UPDATE `pm_user_templates` SET `RelayOnCheck2`='true' WHERE `RelayOnCheck`='y';
ALTER TABLE `pm_user_templates` DROP `RelayOnCheck`;
ALTER TABLE `pm_user_templates` CHANGE `RelayOnCheck2` `RelayOnCheck` ENUM('false','true') DEFAULT 'false' NOT NULL;
ALTER TABLE `pm_user_templates`
    ADD `VirusCheckIn` ENUM('false','true') AFTER `AddrType`,
    ADD `VirusCheckOut` ENUM('false','true') AFTER `VirusCheckIn`;

--
-- Table structure update for table 'pm_users'
--

ALTER TABLE `pm_users` ADD `RelayOnAuth2` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `RelayOnAuth`;
UPDATE `pm_users` SET `RelayOnAuth2`='false' WHERE `RelayOnAuth`='n';
UPDATE `pm_users` SET `RelayOnAuth2`='true' WHERE `RelayOnAuth`='y';
ALTER TABLE `pm_users` DROP `RelayOnAuth`;
ALTER TABLE `pm_users` CHANGE `RelayOnAuth2` `RelayOnAuth` ENUM('false','true') DEFAULT 'true' NOT NULL;

ALTER TABLE `pm_users` ADD `RelayOnCheck2` ENUM('false','true') DEFAULT 'false' NOT NULL AFTER `RelayOnCheck`;
UPDATE `pm_users` SET `RelayOnCheck2`='false' WHERE `RelayOnCheck`='n';
UPDATE `pm_users` SET `RelayOnCheck2`='true' WHERE `RelayOnCheck`='y';
ALTER TABLE `pm_users` DROP `RelayOnCheck`;
ALTER TABLE `pm_users` CHANGE `RelayOnCheck2` `RelayOnCheck` ENUM('false','true') DEFAULT 'false' NOT NULL;


-- Change SET()-Fields to TINYINT(1)
-- This has to be done in: pm_alias_info_fields, pm_customer_info_fields,
-- pm_site_info_fields, pm_site_templates, pm_sites, pm_user_info_fields, pm_user_templates,
-- pm_users


--
-- Table structure update for table 'pm_alias_info_fields'
--

ALTER TABLE `pm_alias_info_fields` ADD `ACL_Reseller2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `ACL_Reseller`;
UPDATE `pm_alias_info_fields` SET `ACL_Reseller2`=1 WHERE FIND_IN_SET('r',`ACL_Reseller`) > 0;
UPDATE `pm_alias_info_fields` SET `ACL_Reseller2`=2 WHERE FIND_IN_SET('w',`ACL_Reseller`) > 0;
ALTER TABLE `pm_alias_info_fields` DROP `ACL_Reseller`;
ALTER TABLE `pm_alias_info_fields` CHANGE `ACL_Reseller2` `ACL_Reseller` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;

ALTER TABLE `pm_alias_info_fields` ADD `ACL_Customer2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `ACL_Customer`;
UPDATE `pm_alias_info_fields` SET `ACL_Customer2`=1 WHERE FIND_IN_SET('r',`ACL_Customer`) > 0;
UPDATE `pm_alias_info_fields` SET `ACL_Customer2`=2 WHERE FIND_IN_SET('w',`ACL_Customer`) > 0;
ALTER TABLE `pm_alias_info_fields` DROP `ACL_Customer`;
ALTER TABLE `pm_alias_info_fields` CHANGE `ACL_Customer2` `ACL_Customer` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;

--
-- Table structure update for table 'pm_customer_info_fields'
--

ALTER TABLE `pm_customer_info_fields` ADD `ACL_Reseller2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `ACL_Reseller`;
UPDATE `pm_customer_info_fields` SET `ACL_Reseller2`=1 WHERE FIND_IN_SET('r',`ACL_Reseller`) > 0;
UPDATE `pm_customer_info_fields` SET `ACL_Reseller2`=2 WHERE FIND_IN_SET('w',`ACL_Reseller`) > 0;
ALTER TABLE `pm_customer_info_fields` DROP `ACL_Reseller`;
ALTER TABLE `pm_customer_info_fields` CHANGE `ACL_Reseller2` `ACL_Reseller` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;

--
-- Table structure update for table 'pm_site_info_fields'
--

ALTER TABLE `pm_site_info_fields` ADD `ACL_Reseller2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `ACL_Reseller`;
UPDATE `pm_site_info_fields` SET `ACL_Reseller2`=1 WHERE FIND_IN_SET('r',`ACL_Reseller`) > 0;
UPDATE `pm_site_info_fields` SET `ACL_Reseller2`=2 WHERE FIND_IN_SET('w',`ACL_Reseller`) > 0;
ALTER TABLE `pm_site_info_fields` DROP `ACL_Reseller`;
ALTER TABLE `pm_site_info_fields` CHANGE `ACL_Reseller2` `ACL_Reseller` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;

ALTER TABLE `pm_site_info_fields` ADD `ACL_Customer2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `ACL_Customer`;
UPDATE `pm_site_info_fields` SET `ACL_Customer2`=1 WHERE FIND_IN_SET('r',`ACL_Customer`) > 0;
UPDATE `pm_site_info_fields` SET `ACL_Customer2`=2 WHERE FIND_IN_SET('w',`ACL_Customer`) > 0;
ALTER TABLE `pm_site_info_fields` DROP `ACL_Customer`;
ALTER TABLE `pm_site_info_fields` CHANGE `ACL_Customer2` `ACL_Customer` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;

--
-- Table structure update for table 'pm_site_templates'
--

ALTER TABLE `pm_site_templates` ADD `AddrType2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `AddrType`;
UPDATE `pm_site_templates` SET `AddrType2`=`AddrType2` | 1 WHERE FIND_IN_SET('p',`AddrType`) > 0;
UPDATE `pm_site_templates` SET `AddrType2`=`AddrType2` | 2 WHERE FIND_IN_SET('i',`AddrType`) > 0;
ALTER TABLE `pm_site_templates` DROP `AddrType`;
ALTER TABLE `pm_site_templates` CHANGE `AddrType2` `AddrType` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;

--
-- Table structure update for table 'pm_sites'
--

ALTER TABLE `pm_sites` ADD `AddrType2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `AddrType`;
UPDATE `pm_sites` SET `AddrType2`=`AddrType2` | 1 WHERE FIND_IN_SET('p',`AddrType`) > 0;
UPDATE `pm_sites` SET `AddrType2`=`AddrType2` | 2 WHERE FIND_IN_SET('i',`AddrType`) > 0;
ALTER TABLE `pm_sites` DROP `AddrType`;
ALTER TABLE `pm_sites` CHANGE `AddrType2` `AddrType` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;
UPDATE `pm_sites` SET `AddrType`=`AddrType` | 4; # SMTP for all sites
UPDATE `pm_sites` SET `AddrType`=`AddrType` | 8; # XAMS for all sites

--
-- Table structure update for table 'pm_user_info_fields'
--

ALTER TABLE `pm_user_info_fields` ADD `ACL_Reseller2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `ACL_Reseller`;
UPDATE `pm_user_info_fields` SET `ACL_Reseller2`=1 WHERE FIND_IN_SET('r',`ACL_Reseller`) > 0;
UPDATE `pm_user_info_fields` SET `ACL_Reseller2`=2 WHERE FIND_IN_SET('w',`ACL_Reseller`) > 0;
ALTER TABLE `pm_user_info_fields` DROP `ACL_Reseller`;
ALTER TABLE `pm_user_info_fields` CHANGE `ACL_Reseller2` `ACL_Reseller` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;

ALTER TABLE `pm_user_info_fields` ADD `ACL_Customer2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `ACL_Customer`;
UPDATE `pm_user_info_fields` SET `ACL_Customer2`=1 WHERE FIND_IN_SET('r',`ACL_Customer`) > 0;
UPDATE `pm_user_info_fields` SET `ACL_Customer2`=2 WHERE FIND_IN_SET('w',`ACL_Customer`) > 0;
ALTER TABLE `pm_user_info_fields` DROP `ACL_Customer`;
ALTER TABLE `pm_user_info_fields` CHANGE `ACL_Customer2` `ACL_Customer` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;

ALTER TABLE `pm_user_info_fields` ADD `ACL_User2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `ACL_User`;
UPDATE `pm_user_info_fields` SET `ACL_User2`=1 WHERE FIND_IN_SET('r',`ACL_User`) > 0;
UPDATE `pm_user_info_fields` SET `ACL_User2`=2 WHERE FIND_IN_SET('w',`ACL_User`) > 0;
ALTER TABLE `pm_user_info_fields` DROP `ACL_User`;
ALTER TABLE `pm_user_info_fields` CHANGE `ACL_User2` `ACL_User` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;

--
-- Table structure update for table 'pm_user_templates'
--

ALTER TABLE `pm_user_templates` ADD `AddrType2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `AddrType`;
UPDATE `pm_user_templates` SET `AddrType2`=`AddrType2` | 1 WHERE FIND_IN_SET('p',`AddrType`) > 0;
UPDATE `pm_user_templates` SET `AddrType2`=`AddrType2` | 2 WHERE FIND_IN_SET('i',`AddrType`) > 0;
ALTER TABLE `pm_user_templates` DROP `AddrType`;
ALTER TABLE `pm_user_templates` CHANGE `AddrType2` `AddrType` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;

--
-- Table structure update for table 'pm_users'
--

ALTER TABLE `pm_users` ADD `AddrType2` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL AFTER `AddrType`;
UPDATE `pm_users` SET `AddrType2`=`AddrType2` | 1 WHERE FIND_IN_SET('p',`AddrType`) > 0;
UPDATE `pm_users` SET `AddrType2`=`AddrType2` | 2 WHERE FIND_IN_SET('i',`AddrType`) > 0;
ALTER TABLE `pm_users` DROP `AddrType`;
ALTER TABLE `pm_users` CHANGE `AddrType2` `AddrType` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL;
UPDATE `pm_users` SET `AddrType`=`AddrType` | 4; # SMTP for all users
UPDATE `pm_users` SET `AddrType`=`AddrType` | 8; # XAMS for all users
