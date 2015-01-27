ALTER TABLE `?:conf_class_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
UPDATE `?:conf_class_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:conf_class_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';
ALTER TABLE `?:conf_group_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
UPDATE `?:conf_group_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:conf_group_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';
ALTER TABLE `?:conf_step_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
UPDATE `?:conf_step_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:conf_step_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';
