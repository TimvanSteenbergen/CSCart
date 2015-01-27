ALTER TABLE `?:companies` DROP KEY `catalog_mode`;
ALTER TABLE `?:companies` DROP `catalog_mode`;
ALTER TABLE `?:settings_vendor_values` CHANGE `value` `value` text NOT NULL;

DELETE FROM `?:settings_descriptions` WHERE object_id IN (SELECT object_id FROM `?:settings_objects` WHERE name IN ('product_detailed_image_width', 'product_detailed_image_height')) AND object_type='O';
DELETE FROM `?:settings_objects` WHERE name IN ('product_detailed_image_width', 'product_detailed_image_height');

