ALTER TABLE `?:aff_banner_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
UPDATE `?:aff_banner_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:aff_banner_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';
ALTER TABLE `?:aff_group_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
UPDATE `?:aff_group_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:aff_group_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';
