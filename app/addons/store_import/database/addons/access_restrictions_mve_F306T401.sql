ALTER TABLE `?:access_restriction_reason_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
UPDATE `?:access_restriction_reason_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:access_restriction_reason_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';
