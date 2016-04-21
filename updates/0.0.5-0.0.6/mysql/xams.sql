ALTER TABLE `xams`.`pm_site_admins` ADD `Locked` ENUM('n','y') NOT NULL, ADD `Failures` INT(11) NOT NULL;
ALTER TABLE `xams`.`pm_users` ADD `RelayOnCheck` ENUM('n','y') DEFAULT 'n' NOT NULL AFTER `RelayOnAuth`;
CREATE TABLE pm_log (
  ID int(11) NOT NULL auto_increment,
  GID int(11) default NULL,
  SID int(11) default NULL,
  UID int(11) default NULL,
  MsgType enum('Login','Selection','Insertion','Update','Deletion') NOT NULL default 'Login',
  MsgStatus enum('ok','failed') NOT NULL default 'ok',
  Resource enum('XAMS','SMTP','POP','IMAP') NOT NULL default 'XAMS',
  TIMESTAMP timestamp(14) NOT NULL,
  PRIMARY KEY  (ID),
  KEY GID (GID),
  KEY SID (SID),
  KEY UID (UID),
  KEY MsgType (MsgType),
  KEY MsgStatus (MsgStatus),
  KEY Resource (Resource),
  KEY TIMESTAMP (TIMESTAMP)
) TYPE=MyISAM;
CREATE TABLE pm_log_message (
  LogID int(11) NOT NULL default '0',
  Message text NOT NULL,
  PRIMARY KEY  (LogID),
  FULLTEXT KEY Message (Message)
) TYPE=MyISAM;
