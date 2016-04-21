#
# Table structure for table `pm_alias_info`
#

CREATE TABLE pm_alias_info (
  InfoFieldID tinyint(4) NOT NULL default '0',
  AliasID int(11) NOT NULL default '0',
  Value varchar(255) NOT NULL default '',
  PRIMARY KEY  (InfoFieldID,AliasID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_alias_info_fields`
#

CREATE TABLE pm_alias_info_fields (
  ID tinyint(4) NOT NULL auto_increment,
  Name varchar(30) NOT NULL default '',
  LdapName varchar(30) NOT NULL default '',
  ACL_Reseller set('r','w') NOT NULL default '',
  ACL_Customer set('r','w') NOT NULL default '',
  Ord tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY Name (Name),
  KEY LdapName (LdapName),
  KEY Ord (Ord)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_customer_info`
#

CREATE TABLE pm_customer_info (
  InfoFieldID tinyint(4) NOT NULL default '0',
  CustomerID int(11) NOT NULL default '0',
  Value varchar(255) NOT NULL default '',
  PRIMARY KEY  (InfoFieldID,CustomerID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_customer_info_fields`
#

CREATE TABLE pm_customer_info_fields (
  ID tinyint(4) NOT NULL auto_increment,
  Name varchar(30) NOT NULL default '',
  LdapName varchar(30) NOT NULL default '',
  ACL_Reseller set('r','w') NOT NULL default '',
  Ord tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY Name (Name),
  KEY LdapName (LdapName),
  KEY Ord (Ord)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_dns`
#

CREATE TABLE pm_dns (
  ID int(11) NOT NULL auto_increment,
  Name varchar(150) NOT NULL default '',
  ZoneType enum('m','s','d') NOT NULL default 'm',
  MasterDNS varchar(255) NOT NULL default '',
  ZoneAdmin varchar(255) NOT NULL default '',
  Serial int(11) NOT NULL default '0',
  SerialAutomatic enum('n','y') NOT NULL default 'n',
  TTL int(11) NOT NULL default '0',
  Refresh int(11) NOT NULL default '0',
  Retry int(11) NOT NULL default '0',
  Expire int(11) NOT NULL default '0',
  NTTL int(11) NOT NULL default '0',
  Changed enum('n','y') NOT NULL default 'y',
  Added datetime default NULL,
  Updated timestamp(14) NOT NULL,
  PRIMARY KEY  (ID),
  UNIQUE KEY Name (Name),
  KEY ZoneType (ZoneType),
  KEY Changed (Changed)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_dns_records`
#

CREATE TABLE pm_dns_records (
  ID int(11) NOT NULL auto_increment,
  DNSID int(11) NOT NULL default '0',
  Name varchar(255) NOT NULL default '',
  Type enum('A','AAAA','CNAME','HINFO','MX','NS','PTR','TXT') NOT NULL default 'A',
  Parameter1 varchar(255) NOT NULL default '',
  Parameter2 varchar(255) NOT NULL default '',
  Comment varchar(255) NOT NULL default '',
  PRIMARY KEY  (ID),
  KEY Type (Type),
  KEY DNSID (DNSID),
  KEY Name (Name)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_logs_c_admins`
#

CREATE TABLE pm_logs_c_admins (
  LogID int(11) NOT NULL default '0',
  AdminID int(11) NOT NULL default '0',
  UNIQUE KEY LogID (LogID,AdminID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_logs_c_customers`
#

CREATE TABLE pm_logs_c_customers (
  LogID int(11) NOT NULL default '0',
  CustomerID int(11) NOT NULL default '0',
  UNIQUE KEY LogID (LogID,CustomerID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_logs_c_resellers`
#

CREATE TABLE pm_logs_c_resellers (
  LogID int(11) NOT NULL default '0',
  ResellerID int(11) NOT NULL default '0',
  UNIQUE KEY LogID (LogID,ResellerID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_logs_c_unknowns`
#

CREATE TABLE pm_logs_c_unknowns (
  LogID int(11) NOT NULL default '0',
  PRIMARY KEY  (LogID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_logs_c_users`
#

CREATE TABLE pm_logs_c_users (
  LogID int(11) NOT NULL default '0',
  UserID int(11) NOT NULL default '0',
  UNIQUE KEY LogID (LogID,UserID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_preferences`
#

CREATE TABLE pm_preferences (
  LogLevel tinyint(2) NOT NULL default '0',
  LogLines smallint(3) NOT NULL default '0',
  NewVersionCheck enum('n','y') NOT NULL default 'n',
  LastVersionCheck date NOT NULL default '0000-00-00',
  DefaultLanguage varchar(30) NOT NULL default '',
  OnlineAbout enum('n','y') NOT NULL default 'n',
  LoginWelcome varchar(50) NOT NULL default ''
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_reseller_info`
#

CREATE TABLE pm_reseller_info (
  InfoFieldID tinyint(4) NOT NULL default '0',
  ResellerID int(11) NOT NULL default '0',
  Value varchar(255) NOT NULL default '',
  PRIMARY KEY  (InfoFieldID,ResellerID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_reseller_info_fields`
#

CREATE TABLE pm_reseller_info_fields (
  ID tinyint(4) NOT NULL auto_increment,
  Name varchar(30) NOT NULL default '',
  LdapName varchar(30) NOT NULL default '',
  Ord tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY Name (Name),
  KEY LdapName (LdapName),
  KEY Ord (Ord)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_resellers`
#

CREATE TABLE pm_resellers (
  ID int(11) NOT NULL auto_increment,
  Name varchar(100) NOT NULL default '',
  Password varchar(32) NOT NULL default '',
  Locked enum('n','y') NOT NULL default 'n',
  Failures int(11) NOT NULL default '0',
  MaxCustomers int(11) default NULL,
  MaxSites int(11) default NULL,
  MaxDomains int(11) default NULL,
  MaxUsers int(11) default NULL,
  MaxAliases int(11) default NULL,
  MaxQuota int(11) default NULL,
  MaxSiteQuota int(11) default NULL,
  MaxUserQuota int(11) default NULL,
  Added datetime default NULL,
  Updated timestamp(14) NOT NULL,
  PRIMARY KEY  (ID),
  UNIQUE KEY Name (Name)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_site_info`
#

CREATE TABLE pm_site_info (
  InfoFieldID tinyint(4) NOT NULL default '0',
  SiteID int(11) NOT NULL default '0',
  Value varchar(255) NOT NULL default '',
  PRIMARY KEY  (InfoFieldID,SiteID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_site_info_fields`
#

CREATE TABLE pm_site_info_fields (
  ID tinyint(4) NOT NULL auto_increment,
  Name varchar(30) NOT NULL default '',
  LdapName varchar(30) NOT NULL default '',
  ACL_Reseller set('r','w') NOT NULL default '',
  ACL_Customer set('r','w') NOT NULL default '',
  Ord tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY Name (Name),
  KEY LdapName (LdapName),
  KEY Ord (Ord)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_site_templates`
#

CREATE TABLE pm_site_templates (
  ID int(11) NOT NULL auto_increment,
  AdminID int(11) default NULL,
  ResellerID int(11) default NULL,
  CustomerID int(11) default NULL,
  TemplateName varchar(20) NOT NULL default '',
  Name varchar(100) NOT NULL default '',
  MaxQuota int(11) default NULL,
  MaxUserQuota int(11) default NULL,
  MaxAddr int(11) default NULL,
  MaxAliases int(11) default NULL,
  AddrType set('i','p') NOT NULL default '',
  LeftPart1 varchar(255) NOT NULL default '',
  RightPart1 varchar(255) NOT NULL default '',
  LeftPart2 varchar(255) NOT NULL default '',
  RightPart2 varchar(255) NOT NULL default '',
  LeftPart3 varchar(255) NOT NULL default '',
  RightPart3 varchar(255) NOT NULL default '',
  LeftPart4 varchar(255) NOT NULL default '',
  RightPart4 varchar(255) NOT NULL default '',
  LeftPart5 varchar(255) NOT NULL default '',
  RightPart5 varchar(255) NOT NULL default '',
  Added datetime default NULL,
  Updated timestamp(14) NOT NULL,
  PRIMARY KEY  (ID),
  KEY CustomerID (CustomerID),
  KEY ResellerID (ResellerID),
  KEY AdminID (AdminID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_user_info`
#

CREATE TABLE pm_user_info (
  InfoFieldID tinyint(4) NOT NULL default '0',
  UserID int(11) NOT NULL default '0',
  Value varchar(255) NOT NULL default '',
  PRIMARY KEY  (InfoFieldID,UserID)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_user_info_fields`
#

CREATE TABLE pm_user_info_fields (
  ID tinyint(4) NOT NULL auto_increment,
  Name varchar(30) NOT NULL default '',
  LdapName varchar(30) NOT NULL default '',
  ACL_Reseller set('r','w') NOT NULL default '',
  ACL_Customer set('r','w') NOT NULL default '',
  ACL_User set('r','w') NOT NULL default '',
  Ord tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY Name (Name),
  KEY LdapName (LdapName),
  KEY Ord (Ord)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `pm_user_templates`
#

CREATE TABLE pm_user_templates (
  ID int(11) NOT NULL auto_increment,
  AdminID int(11) default NULL,
  ResellerID int(11) default NULL,
  CustomerID int(11) default NULL,
  TemplateName varchar(20) NOT NULL default '',
  Name varchar(100) NOT NULL default '',
  Password varchar(32) NOT NULL default '',
  Quota int(11) default NULL,
  AddrType set('i','p') NOT NULL default '',
  RelayOnAuth enum('y','n') NOT NULL default 'y',
  RelayOnCheck enum('y','n') NOT NULL default 'n',
  Added datetime default NULL,
  Updated timestamp(14) NOT NULL,
  PRIMARY KEY  (ID),
  KEY CustomerID (CustomerID),
  KEY ResellerID (ResellerID),
  KEY AdminID (AdminID)
) TYPE=MyISAM;
# --------------------------------------------------------


#
# Changes on existing tables
#

ALTER TABLE `pm_global_admins` RENAME `pm_admins`;
ALTER TABLE `pm_site_admins` RENAME `pm_customers`;
ALTER TABLE `pm_sites_c_admins` RENAME `pm_sites_c_customers`;
ALTER TABLE `pm_admins`
    ADD `Locked` ENUM('n','y') DEFAULT 'n' NOT NULL AFTER `Password`,
    ADD `Added` DATETIME AFTER `Locked`,
    ADD `Updated` TIMESTAMP(14) AFTER `Added`;
ALTER TABLE `pm_aliases`
    DROP PRIMARY KEY,
    ADD `ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
    ADD `Added` DATETIME AFTER `RightPart`,
    ADD `Updated` TIMESTAMP(14) AFTER `Added`,
    ADD UNIQUE (`SiteID`,`LeftPart`);
ALTER TABLE `pm_customers`
    ADD `ResellerID` INT(11) NOT NULL AFTER `ID`,
    ADD `Added` DATETIME AFTER `Failures`,
    ADD `Updated` TIMESTAMP(14) AFTER `Added`,
    ADD INDEX (`ResellerID`);
ALTER TABLE `pm_domains`
    ADD `Added` DATETIME AFTER `SiteID`,
    ADD `Updated` TIMESTAMP(14) AFTER `Added`;
INSERT INTO pm_logs_c_admins (LogID, AdminID) SELECT ID, GID FROM pm_log WHERE GID IS NOT NULL AND GID>0;
INSERT INTO pm_logs_c_customers (LogID, CustomerID) SELECT ID, SID FROM pm_log WHERE SID IS NOT NULL AND SID>0;
INSERT INTO pm_logs_c_users (LogID, UserID) SELECT ID, UID FROM pm_log WHERE UID IS NOT NULL AND UID>0;
INSERT INTO pm_logs_c_unknowns (LogID) SELECT ID FROM pm_log WHERE (GID IS NULL OR GID=0) AND (SID IS NULL OR SID=0) AND (UID IS NULL OR UID=0);
ALTER TABLE `pm_log`
    DROP `GID`,
    DROP `SID`,
    DROP `UID`,
    CHANGE `Resource` `Resource` ENUM('XAMS','SMTP','POP','IMAP','PAM','FTP')
        DEFAULT 'XAMS' NOT NULL;
ALTER TABLE `pm_log_message`
    ADD `Name` varchar(255) NOT NULL AFTER `LogID`,
    ADD FULLTEXT(`Name`);
ALTER TABLE `pm_sites`
    ADD `ResellerID` INT(11) NOT NULL AFTER `ID`,
    ADD `MaxUserQuota` INT(11) AFTER `MaxQuota`,
    ADD `Added` DATETIME AFTER `SiteState`,
    ADD `Updated` TIMESTAMP(14) AFTER `Added`,
    ADD INDEX (`ResellerID`);
ALTER TABLE `pm_sites_c_customers`
    CHANGE `AdminID` `CustomerID` INT(11) DEFAULT '0' NOT NULL;
ALTER TABLE `pm_users`
    CHANGE `RelayOnAuth` `RelayOnAuth` ENUM('n','y') DEFAULT 'y' NOT NULL,
    CHANGE `RelayOnCheck` `RelayOnCheck` ENUM('n','y') DEFAULT 'n' NOT NULL,
    ADD `Added` DATETIME AFTER `AccountState`,
    ADD `Updated` TIMESTAMP(14) AFTER `Added`;
INSERT INTO pm_preferences (LogLevel, LogLines, NewVersionCheck,
LastVersionCheck, DefaultLanguage, OnlineAbout, LoginWelcome)
VALUES (3, 13, 'n', '0000-00-00', 'english', 'n', 'Welcome to XAMS');
INSERT INTO pm_resellers (Name, Locked, Added) VALUES ('default', 'y', NOW());
UPDATE pm_sites SET ResellerID=1 WHERE ResellerID IS NULL OR ResellerID==0;
 