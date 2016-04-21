
-- 
-- Database: `xams`
-- Version 0.0.19
--

DROP TABLE IF EXISTS `pm_admins`;
CREATE TABLE `pm_admins` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(100) NOT NULL default '',
  `Password` varchar(32) NOT NULL default '',
  `Locked` enum('false','true') NOT NULL default 'false',
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

DROP TABLE IF EXISTS `pm_alias_info`;
CREATE TABLE `pm_alias_info` (
  `InfoFieldID` tinyint(4) NOT NULL default '0',
  `AliasID` int(11) NOT NULL default '0',
  `Value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`InfoFieldID`,`AliasID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_alias_info_fields`;
CREATE TABLE `pm_alias_info_fields` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `Name` varchar(30) NOT NULL default '',
  `LdapName` varchar(30) NOT NULL default '',
  `ACL_Reseller` tinyint(1) unsigned NOT NULL default '0',
  `ACL_Customer` tinyint(1) unsigned NOT NULL default '0',
  `Ord` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `Name` (`Name`),
  KEY `LdapName` (`LdapName`),
  KEY `Ord` (`Ord`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `pm_aliases`;
CREATE TABLE `pm_aliases` (
  `ID` int(11) NOT NULL auto_increment,
  `SiteID` int(11) NOT NULL default '0',
  `LeftPart` varchar(255) NOT NULL default '',
  `RightPart` text NOT NULL,
  `BounceForward` enum('false','true') NOT NULL default 'false',
  `BlackHole` enum('false','true') NOT NULL default 'false',
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `SiteID` (`SiteID`,`LeftPart`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

DROP TABLE IF EXISTS `pm_customer_info`;
CREATE TABLE `pm_customer_info` (
  `InfoFieldID` tinyint(4) NOT NULL default '0',
  `CustomerID` int(11) NOT NULL default '0',
  `Value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`InfoFieldID`,`CustomerID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_customer_info_fields`;
CREATE TABLE `pm_customer_info_fields` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `Name` varchar(30) NOT NULL default '',
  `LdapName` varchar(30) NOT NULL default '',
  `ACL_Reseller` tinyint(1) unsigned NOT NULL default '0',
  `ACL_Customer` tinyint(1) unsigned NOT NULL default '0',
  `Ord` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `Name` (`Name`),
  KEY `LdapName` (`LdapName`),
  KEY `Ord` (`Ord`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `pm_customers`;
CREATE TABLE `pm_customers` (
  `ID` int(11) NOT NULL auto_increment,
  `ResellerID` int(11) NOT NULL default '0',
  `Name` varchar(100) NOT NULL default '',
  `Password` varchar(32) NOT NULL default '',
  `Locked` enum('false','true') NOT NULL default 'false',
  `Failures` int(11) NOT NULL default '0',
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`),
  KEY `ResellerID` (`ResellerID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

DROP TABLE IF EXISTS `pm_dns`;
CREATE TABLE `pm_dns` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(150) NOT NULL default '',
  `ZoneType` char(1) NOT NULL default 'm',
  `MasterDNS` varchar(255) NOT NULL default '',
  `ZoneAdmin` varchar(255) NOT NULL default '',
  `Serial` int(11) NOT NULL default '0',
  `SerialAutomatic` enum('false','true') NOT NULL default 'false',
  `TTL` int(11) NOT NULL default '0',
  `Refresh` int(11) NOT NULL default '0',
  `Retry` int(11) NOT NULL default '0',
  `Expire` int(11) NOT NULL default '0',
  `NTTL` int(11) NOT NULL default '0',
  `Changed` enum('false','true') NOT NULL default 'true',
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`),
  KEY `ZoneType` (`ZoneType`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `pm_dns_records`;
CREATE TABLE `pm_dns_records` (
  `ID` int(11) NOT NULL auto_increment,
  `DNSID` int(11) NOT NULL default '0',
  `Name` varchar(255) NOT NULL default '',
  `Type` enum('A','AAAA','CNAME','HINFO','MX','NS','PTR','TXT') NOT NULL default 'A',
  `Parameter1` varchar(255) NOT NULL default '',
  `Parameter2` varchar(255) NOT NULL default '',
  `Comment` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `Type` (`Type`),
  KEY `DNSID` (`DNSID`),
  KEY `Name` (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `pm_domains`;
CREATE TABLE `pm_domains` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(150) NOT NULL default '',
  `SiteID` int(11) NOT NULL default '0',
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`),
  KEY `SiteID` (`SiteID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `pm_exim_filters`;
CREATE TABLE `pm_exim_filters` (
  `UserID` int(11) NOT NULL default '0',
  `Filter` text,
  `Active` enum('false','true') NOT NULL default 'true',
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`UserID`),
  KEY `Active` (`Active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_log`;
CREATE TABLE `pm_log` (
  `ID` int(11) NOT NULL auto_increment,
  `MsgType` enum('Login','Selection','Insertion','Update','Deletion') NOT NULL default 'Login',
  `MsgStatus` enum('ok','failed') NOT NULL default 'ok',
  `Resource` enum('XAMS','SMTP','POP','IMAP','PAM','FTP') NOT NULL default 'XAMS',
  `TIMESTAMP` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  KEY `MsgType` (`MsgType`),
  KEY `MsgStatus` (`MsgStatus`),
  KEY `Resource` (`Resource`),
  KEY `TIMESTAMP` (`TIMESTAMP`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2353 ;

DROP TABLE IF EXISTS `pm_log_message`;
CREATE TABLE `pm_log_message` (
  `LogID` int(11) NOT NULL default '0',
  `Name` varchar(255) NOT NULL default '',
  `Message` text NOT NULL,
  PRIMARY KEY  (`LogID`),
  FULLTEXT KEY `Message` (`Message`),
  FULLTEXT KEY `Name` (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_logs_c_admins`;
CREATE TABLE `pm_logs_c_admins` (
  `LogID` int(11) NOT NULL default '0',
  `AdminID` int(11) NOT NULL default '0',
  UNIQUE KEY `LogID` (`LogID`,`AdminID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_logs_c_customers`;
CREATE TABLE `pm_logs_c_customers` (
  `LogID` int(11) NOT NULL default '0',
  `CustomerID` int(11) NOT NULL default '0',
  UNIQUE KEY `LogID` (`LogID`,`CustomerID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_logs_c_resellers`;
CREATE TABLE `pm_logs_c_resellers` (
  `LogID` int(11) NOT NULL default '0',
  `ResellerID` int(11) NOT NULL default '0',
  UNIQUE KEY `LogID` (`LogID`,`ResellerID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_logs_c_unknowns`;
CREATE TABLE `pm_logs_c_unknowns` (
  `LogID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`LogID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_logs_c_users`;
CREATE TABLE `pm_logs_c_users` (
  `LogID` int(11) NOT NULL default '0',
  `UserID` int(11) NOT NULL default '0',
  UNIQUE KEY `LogID` (`LogID`,`UserID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_preferences`;
CREATE TABLE `pm_preferences` (
  `LogLevel` tinyint(2) NOT NULL default '0',
  `Admin` varchar(50) NOT NULL,
  `LogLines` smallint(3) NOT NULL default '0',
  `NewVersionCheck` enum('false','true') NOT NULL default 'false',
  `LastVersionCheck` date NOT NULL default '0000-00-00',
  `LastNewsCheck` date NOT NULL default '0000-00-00',
  `DefaultLanguage` varchar(30) NOT NULL default '',
  `OnlineNews` enum('false','true') NOT NULL default 'false',
  `LoginWelcome` varchar(50) NOT NULL default '',
  `SpamScore` decimal(10,0) NOT NULL,
  `HighSpamScore` decimal(10,0) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_properties`;
CREATE TABLE `pm_properties` (
  `Property` varchar(100) NOT NULL,
  `Value` varchar(100) NOT NULL,
  PRIMARY KEY  (`Property`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_reseller_info`;
CREATE TABLE `pm_reseller_info` (
  `InfoFieldID` tinyint(4) NOT NULL default '0',
  `ResellerID` int(11) NOT NULL default '0',
  `Value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`InfoFieldID`,`ResellerID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_reseller_info_fields`;
CREATE TABLE `pm_reseller_info_fields` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `Name` varchar(30) NOT NULL default '',
  `LdapName` varchar(30) NOT NULL default '',
  `ACL_Reseller` tinyint(1) unsigned NOT NULL default '0',
  `Ord` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `Name` (`Name`),
  KEY `LdapName` (`LdapName`),
  KEY `Ord` (`Ord`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `pm_resellers`;
CREATE TABLE `pm_resellers` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(100) NOT NULL default '',
  `Password` varchar(32) NOT NULL default '',
  `Locked` enum('false','true') NOT NULL default 'false',
  `Failures` int(11) NOT NULL default '0',
  `MaxCustomers` int(11) default NULL,
  `MaxSites` int(11) default NULL,
  `MaxDomains` int(11) default NULL,
  `MaxUsers` int(11) default NULL,
  `MaxAliases` int(11) default NULL,
  `MaxQuota` int(11) default NULL,
  `MaxSiteQuota` int(11) default NULL,
  `MaxUserQuota` int(11) default NULL,
  `VirusCheckIn` enum('false','true') NOT NULL default 'true',
  `VirusCheckOut` enum('false','true') NOT NULL default 'true',
  `SpamCheckIn` enum('false','true') NOT NULL default 'true',
  `SpamCheckOut` enum('false','true') NOT NULL default 'true',
  `SpamScore` decimal(10,0) NOT NULL,
  `HighSpamScore` decimal(10,0) NOT NULL,
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `pm_site_info`;
CREATE TABLE `pm_site_info` (
  `InfoFieldID` tinyint(4) NOT NULL default '0',
  `SiteID` int(11) NOT NULL default '0',
  `Value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`InfoFieldID`,`SiteID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_site_info_fields`;
CREATE TABLE `pm_site_info_fields` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `Name` varchar(30) NOT NULL default '',
  `LdapName` varchar(30) NOT NULL default '',
  `ACL_Reseller` tinyint(1) unsigned NOT NULL default '0',
  `ACL_Customer` tinyint(1) unsigned NOT NULL default '0',
  `Ord` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `Name` (`Name`),
  KEY `LdapName` (`LdapName`),
  KEY `Ord` (`Ord`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `pm_site_templates`;
CREATE TABLE `pm_site_templates` (
  `ID` int(11) NOT NULL auto_increment,
  `AdminID` int(11) default NULL,
  `ResellerID` int(11) default NULL,
  `TemplateName` varchar(20) NOT NULL default '',
  `Name` varchar(100) NOT NULL default '',
  `MaxQuota` int(11) default NULL,
  `MaxUserQuota` int(11) default NULL,
  `MaxAddr` int(11) default NULL,
  `MaxAliases` int(11) default NULL,
  `AddrType` tinyint(1) unsigned NOT NULL default '0',
  `VirusCheckIn` enum('false','true') NOT NULL default 'true',
  `VirusCheckOut` enum('false','true') NOT NULL default 'true',
  `SpamCheckIn` enum('false','true') NOT NULL default 'true',
  `SpamCheckOut` enum('false','true') NOT NULL default 'true',
  `SpamScore` decimal(10,0) NOT NULL,
  `HighSpamScore` decimal(10,0) NOT NULL,
  `LeftPart1` varchar(255) NOT NULL default '',
  `RightPart1` varchar(255) NOT NULL default '',
  `BounceForward1` enum('false','true') NOT NULL default 'false',
  `LeftPart2` varchar(255) NOT NULL default '',
  `RightPart2` varchar(255) NOT NULL default '',
  `BounceForward2` enum('false','true') NOT NULL default 'false',
  `LeftPart3` varchar(255) NOT NULL default '',
  `RightPart3` varchar(255) NOT NULL default '',
  `BounceForward3` enum('false','true') NOT NULL default 'false',
  `LeftPart4` varchar(255) NOT NULL default '',
  `RightPart4` varchar(255) NOT NULL default '',
  `BounceForward4` enum('false','true') NOT NULL default 'false',
  `LeftPart5` varchar(255) NOT NULL default '',
  `RightPart5` varchar(255) NOT NULL default '',
  `BounceForward5` enum('false','true') NOT NULL default 'false',
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  KEY `ResellerID` (`ResellerID`),
  KEY `AdminID` (`AdminID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

DROP TABLE IF EXISTS `pm_sites`;
CREATE TABLE `pm_sites` (
  `ID` int(11) NOT NULL auto_increment,
  `ResellerID` int(11) NOT NULL default '0',
  `Name` varchar(100) NOT NULL default '',
  `MaxQuota` int(11) default NULL,
  `MaxUserQuota` int(11) default NULL,
  `MaxAddr` int(11) default NULL,
  `MaxAliases` int(11) default NULL,
  `AddrType` tinyint(1) unsigned NOT NULL default '0',
  `VirusCheckIn` enum('false','true') NOT NULL default 'true',
  `VirusCheckOut` enum('false','true') NOT NULL default 'true',
  `SpamCheckIn` enum('false','true') NOT NULL default 'true',
  `SpamCheckOut` enum('false','true') NOT NULL default 'true',
  `SpamScore` decimal(10,0) NOT NULL,
  `HighSpamScore` decimal(10,0) NOT NULL,
  `SiteState` enum('default','locked','lockedbounce') NOT NULL default 'default',
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`),
  KEY `ResellerID` (`ResellerID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `pm_sites_c_customers`;
CREATE TABLE `pm_sites_c_customers` (
  `CustomerID` int(11) NOT NULL default '0',
  `SiteID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`CustomerID`,`SiteID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_user_info`;
CREATE TABLE `pm_user_info` (
  `InfoFieldID` tinyint(4) NOT NULL default '0',
  `UserID` int(11) NOT NULL default '0',
  `Value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`InfoFieldID`,`UserID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pm_user_info_fields`;
CREATE TABLE `pm_user_info_fields` (
  `ID` tinyint(4) NOT NULL auto_increment,
  `Name` varchar(30) NOT NULL default '',
  `LdapName` varchar(30) NOT NULL default '',
  `ACL_Reseller` tinyint(1) unsigned NOT NULL default '0',
  `ACL_Customer` tinyint(1) unsigned NOT NULL default '0',
  `ACL_User` tinyint(1) unsigned NOT NULL default '0',
  `Ord` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `Name` (`Name`),
  KEY `LdapName` (`LdapName`),
  KEY `Ord` (`Ord`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `pm_user_templates`;
CREATE TABLE `pm_user_templates` (
  `ID` int(11) NOT NULL auto_increment,
  `AdminID` int(11) default NULL,
  `ResellerID` int(11) default NULL,
  `CustomerID` int(11) default NULL,
  `TemplateName` varchar(20) NOT NULL default '',
  `Name` varchar(100) NOT NULL default '',
  `Password` varchar(32) NOT NULL default '',
  `Quota` int(11) default NULL,
  `AddrType` tinyint(1) unsigned NOT NULL default '0',
  `VirusCheckIn` enum('false','true') default NULL,
  `VirusCheckOut` enum('false','true') default NULL,
  `SpamCheckIn` enum('false','true') default NULL,
  `SpamCheckOut` enum('false','true') default NULL,
  `SpamScore` decimal(10,0) NOT NULL default '5',
  `HighSpamScore` decimal(10,0) NOT NULL default '15',
  `RelayOnAuth` enum('false','true') NOT NULL default 'true',
  `RelayOnCheck` enum('false','true') NOT NULL default 'false',
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `ResellerID` (`ResellerID`),
  KEY `AdminID` (`AdminID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

DROP TABLE IF EXISTS `pm_users`;
CREATE TABLE `pm_users` (
  `ID` int(11) NOT NULL auto_increment,
  `SiteID` int(11) NOT NULL default '0',
  `UniqueName` varchar(100) default NULL,
  `Name` varchar(100) NOT NULL default '',
  `Password` varchar(32) NOT NULL default '',
  `Quota` int(11) default NULL,
  `UsedQuota` int(11) default NULL,
  `AddrType` tinyint(1) unsigned NOT NULL default '0',
  `VirusCheckIn` enum('false','true') default NULL,
  `VirusCheckOut` enum('false','true') default NULL,
  `SpamCheckIn` enum('false','true') default NULL,
  `SpamCheckOut` enum('false','true') default NULL,
  `SpamScore` decimal(10,0) NOT NULL,
  `HighSpamScore` decimal(10,0) NOT NULL,
  `RelayOnAuth` enum('false','true') NOT NULL default 'true',
  `RelayOnCheck` enum('false','true') NOT NULL default 'false',
  `AutoReply` enum('false','true') NOT NULL default 'false',
  `AutoReplySubject` varchar(50) NOT NULL default '',
  `AutoReplyText` text,
  `AccountState` enum('default','locked','lockedbounce') NOT NULL default 'default',
  `Added` datetime default NULL,
  `Updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `DomainID` (`SiteID`,`Name`),
  UNIQUE KEY `UniqueName` (`UniqueName`),
  KEY `Password` (`Password`),
  KEY `AutoReply` (`AutoReply`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

