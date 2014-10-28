ALTER TABLE `?:currencies` CHANGE `coefficient` `coefficient` double(12,5) NOT NULL DEFAULT '1.00000';
ALTER TABLE `?:product_features_values` CHANGE `value_int` `value_int` double(12,2) DEFAULT NULL;
ALTER TABLE `?:settings_objects` CHANGE `value` `value` text NOT NULL DEFAULT '';
DROP TABLE `?:settings_vendor_values`;

INSERT INTO `?:payment_processors` (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES ('89', 'Vsevcredit', 'vsevcredit.php', 'cc_outside.tpl', 'vsevcredit.tpl', 'Y', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
INSERT INTO `?:states` (state_id, country_code, code, status) VALUES ('503', 'GB', 'IOS', 'A') ON DUPLICATE KEY UPDATE `state_id` = `state_id`;
DELETE FROM `?:settings_objects` WHERE object_id='92';
DELETE FROM `?:settings_objects` WHERE object_id='142';
DELETE FROM `?:settings_objects` WHERE object_id='181';
