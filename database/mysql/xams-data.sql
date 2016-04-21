INSERT INTO pm_properties VALUES ('database_structure', '0.0.20');

INSERT INTO pm_preferences (LogLevel, Admin, LogLines, NewVersionCheck,
LastVersionCheck, DefaultLanguage, OnlineNews, LoginWelcome, SpamScore, HighSpamScore)
VALUES (3, 'admin', 13, 'true', '0000-00-00', 'english', 'false', 'Welcome to XAMS', '5', '15');

-- The following line insert a test account to the database (Username: admin / Password: admin)
INSERT INTO pm_admins (Name, Password, Added) VALUES ("admin", MD5("admin"), NOW());

