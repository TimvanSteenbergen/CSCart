ALTER TABLE `?:rma_property_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
UPDATE `?:rma_property_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:rma_property_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';