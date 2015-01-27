ALTER TABLE `?:settings_objects` CHANGE `value` `value` text NOT NULL;
DROP TABLE `?:sitemap_descriptions`;

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
DELETE FROM `?:settings_objects` WHERE object_id='200';
