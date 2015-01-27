ALTER TABLE `?:banners` ADD COLUMN `position` smallint(5) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `?:banner_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
UPDATE `?:banner_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:banner_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';