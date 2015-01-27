ALTER TABLE `?:categories` DROP `owner_id`;
ALTER TABLE `?:payments` ADD `company_id` int(11) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `?:product_features_values` CHANGE `value_int` `value_int` float(12,2) DEFAULT NULL;
ALTER TABLE `?:product_filters` ADD `display_more_count` smallint(5) unsigned NOT NULL DEFAULT '20';
ALTER TABLE `?:products` DROP `owner_id`;
ALTER TABLE `?:tax_rates` DROP `owner_id`;

UPDATE `?:status_data` SET value='Y' WHERE status='C' AND type='O' AND param='notify';
UPDATE `?:status_data` SET value='Y' WHERE status='B' AND type='O' AND param='notify';
UPDATE `?:status_data` SET value='Y' WHERE status='D' AND type='O' AND param='notify';
UPDATE `?:status_data` SET value='Y' WHERE status='I' AND type='O' AND param='notify';
UPDATE `?:status_data` SET value='Y' WHERE status='F' AND type='O' AND param='notify';
UPDATE `?:status_data` SET value='Y' WHERE status='O' AND type='O' AND param='notify';
UPDATE `?:status_data` SET value='Y' WHERE status='P' AND type='O' AND param='notify';
UPDATE `?:states` SET country_code='IT', code='PV', status='A' WHERE state_id='449';

INSERT INTO `?:payment_processors` (processor, processor_script, processor_template, admin_template, callback, type) VALUES ('Plati Doma', 'pay_at_home.php', 'cc_outside.tpl', 'pay_at_home.tpl', 'Y', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
INSERT INTO `?:payment_processors` (processor, processor_script, processor_template, admin_template, callback, type) VALUES ('Qiwi', 'qiwi.php', 'qiwi.tpl', 'qiwi.tpl', 'Y', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
INSERT INTO `?:payment_processors` (processor, processor_script, processor_template, admin_template, callback, type) VALUES ('RBK Money', 'rbk.php', 'cc_outside.tpl', 'rbk.tpl', 'Y', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
INSERT INTO `?:shipping_services` (status, carrier, module, code, sp_file) VALUES ('A', 'USP', 'usps', 'Priority Mail Regional Rate', '') ON DUPLICATE KEY UPDATE `service_id` = `service_id`;
INSERT INTO `?:states` (state_id, country_code, code, status) VALUES ('447', 'IT', 'PA', 'A') ON DUPLICATE KEY UPDATE `state_id` = `state_id`;

REPLACE INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('manage_recurring_plans', 'Manage recurring plans', 'EN', '1');
REPLACE INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('manage_subscriptions', 'Manage subscriptions', 'EN', '1');
REPLACE INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('manage_seo_rules', 'Manage SEO rules', 'EN', '1');
REPLACE INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('view_banners', 'View banners', 'EN', '7');
REPLACE INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('view_discussions', 'View comments and reviews', 'EN', '6');
REPLACE INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('view_events', 'View events', 'EN', '1');
REPLACE INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('view_news', 'View news', 'EN', '6');
REPLACE INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('view_statistics', 'View statistics', 'EN', '1');
REPLACE INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('manage_statistics', 'Manage statistics', 'EN', '1');
