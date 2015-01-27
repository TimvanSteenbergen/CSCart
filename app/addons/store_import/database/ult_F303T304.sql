ALTER TABLE `?:settings_objects` CHANGE `value` `value` text NOT NULL;
DROP TABLE `?:sitemap_descriptions`;
DROP TABLE `?:vendor_payouts`;

INSERT INTO `?:payment_processors` (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES ('90', 'Pay4Later', 'pay4later.php', 'cc_outside.tpl', 'pay4later.tpl', 'N', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
INSERT INTO `?:payment_processors` (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES ('91', 'Yes Credit', 'yes_credit.php', 'cc_outside.tpl', 'yes_credit.tpl', 'N', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
INSERT INTO `?:status_data` (status, type, param, value) VALUES ('B', 'O', 'remove_cc_info', 'Y') ON DUPLICATE KEY UPDATE `status` = `status`;
INSERT INTO `?:status_data` (status, type, param, value) VALUES ('I', 'O', 'remove_cc_info', 'Y') ON DUPLICATE KEY UPDATE `status` = `status`;
INSERT INTO `?:status_data` (status, type, param, value) VALUES ('C', 'O', 'remove_cc_info', 'Y') ON DUPLICATE KEY UPDATE `status` = `status`;
INSERT INTO `?:status_data` (status, type, param, value) VALUES ('D', 'O', 'remove_cc_info', 'Y') ON DUPLICATE KEY UPDATE `status` = `status`;
INSERT INTO `?:status_data` (status, type, param, value) VALUES ('F', 'O', 'remove_cc_info', 'Y') ON DUPLICATE KEY UPDATE `status` = `status`;
INSERT INTO `?:status_data` (status, type, param, value) VALUES ('O', 'O', 'remove_cc_info', 'Y') ON DUPLICATE KEY UPDATE `status` = `status`;
INSERT INTO `?:status_data` (status, type, param, value) VALUES ('P', 'O', 'remove_cc_info', 'Y') ON DUPLICATE KEY UPDATE `status` = `status`;
INSERT INTO `?:status_data` (status, type, param, value) VALUES ('A', 'O', 'remove_cc_info', 'Y') ON DUPLICATE KEY UPDATE `status` = `status`;
INSERT INTO `?:privileges` (privilege, is_default) VALUES ('manage_stores', 'Y') ON DUPLICATE KEY UPDATE `privilege` = `privilege`;
INSERT INTO `?:privileges` (privilege, is_default) VALUES ('view_stores', 'Y') ON DUPLICATE KEY UPDATE `privilege` = `privilege`;
INSERT INTO `?:privilege_descriptions` (`privilege`, `description`, `lang_code`, `section_id`) VALUES ('manage_stores', 'Manage stores', 'EN', 10), ('view_stores', 'View stores', 'EN', 10);

DELETE FROM `?:settings_objects` WHERE object_id='200';
DELETE FROM `?:privileges` WHERE privilege='view_suppliers';
DELETE FROM `?:privileges` WHERE privilege='manage_suppliers';
DELETE FROM `?:privileges` WHERE privilege='view_vendors';
DELETE FROM `?:privileges` WHERE privilege='manage_vendors';
DELETE FROM `?:privileges` WHERE privilege='view_payouts';
DELETE FROM `?:privileges` WHERE privilege='manage_payouts';
