ALTER TABLE `pm_users` ADD `UniqueName` varchar(100) DEFAULT NULL AFTER `SiteID`, ADD UNIQUE (`UniqueName`);
ALTER TABLE `pm_aliases` ADD `BlackHole` enum('false','true') NOT NULL default 'false' AFTER `BounceForward`;
