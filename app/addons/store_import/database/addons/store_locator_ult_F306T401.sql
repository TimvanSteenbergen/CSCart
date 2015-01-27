UPDATE `?:store_location_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:store_location_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';
