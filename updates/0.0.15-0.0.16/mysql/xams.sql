-- Updates for SpamCheckIn/Out

ALTER TABLE `pm_resellers`
    ADD `SpamCheckIn` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `VirusCheckOut`,
    ADD `SpamCheckOut` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `SpamCheckIn`;

ALTER TABLE `pm_site_templates`
    ADD `SpamCheckIn` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `VirusCheckOut`,
    ADD `SpamCheckOut` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `SpamCheckIn`;

ALTER TABLE `pm_user_templates`
    ADD `SpamCheckIn` ENUM('false','true') AFTER `VirusCheckOut`,
    ADD `SpamCheckOut` ENUM('false','true') AFTER `SpamCheckIn`;

ALTER TABLE `pm_sites`
    ADD `SpamCheckIn` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `VirusCheckOut`,
    ADD `SpamCheckOut` ENUM('false','true') DEFAULT 'true' NOT NULL AFTER `SpamCheckIn`;

ALTER TABLE `pm_users`
    ADD `SpamCheckIn` ENUM('false','true') AFTER `VirusCheckOut`,
    ADD `SpamCheckOut` ENUM('false','true') AFTER `SpamCheckIn`,
    ADD `AutoReplySubject` varchar(50) NOT NULL default '' AFTER `AutoReply`,
    CHANGE `AutoReplyText` `AutoReplyText` TEXT DEFAULT NULL
    ;

ALTER TABLE `pm_aliases`
    CHANGE `RightPart` `RightPart` TEXT NOT NULL default '';


CREATE TABLE pm_properties (
  Property varchar(100) NOT NULL,
  Value varchar(100) NOT NULL,
  PRIMARY KEY  (Property)
) TYPE=MyISAM;

INSERT INTO pm_properties VALUES ('database_structure', '0.0.16');
