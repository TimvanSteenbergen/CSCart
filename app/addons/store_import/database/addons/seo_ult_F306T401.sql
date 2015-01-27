ALTER TABLE `?:seo_names` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
UPDATE `?:seo_names` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:seo_names` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';
UPDATE `?:settings_objects` SET value='product_file' WHERE name = 'seo_product_type' AND value='category';
