ALTER TABLE `?:giftreg_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
UPDATE `?:giftreg_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:giftreg_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';