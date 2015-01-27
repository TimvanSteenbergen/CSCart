ALTER TABLE `?:addon_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:addons` ADD `unmanaged` tinyint(1) NOT NULL;
ALTER TABLE `?:addons` ADD `has_icon` tinyint(1) NOT NULL;
ALTER TABLE `?:bm_blocks_content` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:bm_blocks_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:bm_grids` DROP `suffix`;
ALTER TABLE `?:bm_grids` DROP `prefix`;
ALTER TABLE `?:bm_grids` ADD `offset` tinyint(4) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `?:bm_grids` ADD `status` varchar(1) NOT NULL DEFAULT 'A';
DROP TABLE IF EXISTS `?:bm_layouts`;
CREATE TABLE `?:bm_layouts` (  `layout_id` int(11) NOT NULL AUTO_INCREMENT,  `name` varchar(64) NOT NULL DEFAULT '',  `is_default` tinyint(4) NOT NULL DEFAULT '0',  `width` tinyint(4) NOT NULL DEFAULT '16',  `theme_name` varchar(64) NOT NULL DEFAULT '',  `preset_id` int(11) unsigned NOT NULL DEFAULT '0',  `company_id` int(11) unsigned NOT NULL,  PRIMARY KEY (`layout_id`),  KEY `is_default` (`is_default`,`company_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `?:bm_locations` DROP `company_id`;
ALTER TABLE `?:bm_locations` ADD `layout_id` int(11) unsigned DEFAULT NULL;
ALTER TABLE `?:bm_locations` ADD `position` smallint(5) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `?:bm_locations_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:category_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:common_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:companies` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:country_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:currency_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:destination_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:language_values` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:localization_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
DROP TABLE IF EXISTS `?:logos`;
CREATE TABLE `?:logos` (  `logo_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,  `layout_id` int(11) NOT NULL DEFAULT '0',  `company_id` int(11) NOT NULL DEFAULT '0',  `type` varchar(32) NOT NULL DEFAULT '',  PRIMARY KEY (`logo_id`),  KEY `type` (`type`,`company_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
TRUNCATE TABLE `?:new_orders`;
ALTER TABLE `?:orders` DROP `title`;
ALTER TABLE `?:orders` DROP `b_title`;
ALTER TABLE `?:orders` DROP `s_title`;
ALTER TABLE `?:orders` ADD `issuer_id` mediumint(8) unsigned DEFAULT NULL;
ALTER TABLE `?:orders` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:page_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:payment_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:privilege_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:privilege_section_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:product_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:product_descriptions` ADD `promo_text` mediumtext NOT NULL;
ALTER TABLE `?:product_feature_variant_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:product_features` ADD `feature_code` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `?:product_features` CHANGE `display_on_product` `display_on_product` char(1) NOT NULL DEFAULT 'Y';
ALTER TABLE `?:product_features` CHANGE `display_on_catalog` `display_on_catalog` char(1) NOT NULL DEFAULT 'Y';
ALTER TABLE `?:product_features` ADD `display_on_header` char(1) NOT NULL DEFAULT 'N';
ALTER TABLE `?:product_features_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
DELETE FROM ?:profile_field_descriptions WHERE object_id IN (SELECT field_id FROM ?:profile_fields WHERE field_type = 'L');
DELETE FROM ?:ult_objects_sharing WHERE share_object_id IN (SELECT field_id FROM ?:profile_fields WHERE field_type = 'L') AND share_object_type = 'profile_fields';
DELETE FROM ?:profile_fields WHERE field_type = 'L';

# fix bug 9534
DROP TABLE IF EXISTS `?:product_features_values_fix`;
CREATE TABLE `?:product_features_values_fix` (
  `feature_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `product_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `variant_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `value` varchar(255) NOT NULL DEFAULT '',
  `value_int` double(12,2) DEFAULT NULL,
  `lang_code` char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`feature_id`, `product_id`, `variant_id`, `lang_code`),
  KEY `fl` (`feature_id`,`lang_code`,`variant_id`,`value`,`value_int`),
  KEY `variant_id` (`variant_id`),
  KEY `lang_code` (`lang_code`),
  KEY `product_id` (`product_id`),
  KEY `fpl` (`feature_id`,`product_id`,`lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `?:product_features_values_fix`
  SELECT * FROM `?:product_features_values`;

DROP TABLE IF EXISTS `?:product_features_values`;
RENAME TABLE `?:product_features_values_fix` TO `?:product_features_values`;

UPDATE `?:product_features` SET display_on_product = 'Y' WHERE display_on_product = '1';
UPDATE `?:product_features` SET display_on_product = 'N' WHERE display_on_product = '0';
UPDATE `?:product_features` SET display_on_catalog = 'N' WHERE display_on_catalog = '0';
UPDATE `?:product_features` SET display_on_catalog = 'Y' WHERE display_on_catalog = '1';

ALTER TABLE `?:product_file_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
DROP TABLE IF EXISTS `?:product_file_folder_descriptions`;
CREATE TABLE `?:product_file_folder_descriptions` (  `folder_id` mediumint(8) unsigned NOT NULL DEFAULT '0',  `lang_code` char(2) NOT NULL DEFAULT '',  `folder_name` varchar(255) NOT NULL DEFAULT '',  PRIMARY KEY (`folder_id`,`lang_code`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `?:product_file_folders`;
CREATE TABLE `?:product_file_folders` (  `folder_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,  `product_id` mediumint(8) unsigned NOT NULL DEFAULT '0',  `position` smallint(5) NOT NULL DEFAULT '0',  `status` char(1) NOT NULL DEFAULT 'A',  PRIMARY KEY (`folder_id`),  KEY `product_id` (`product_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `?:product_files` ADD `folder_id` mediumint(8) unsigned DEFAULT NULL;
ALTER TABLE `?:product_filter_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:product_filter_ranges_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:product_option_variants_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:product_options_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:product_options_exceptions` CHANGE `combination` `combination` text NOT NULL;
ALTER TABLE `?:profile_field_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:profile_fields` ADD `autocomplete_type` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `?:promotion_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:sales_reports_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:sales_reports_table_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:settings_descriptions` CHANGE `object_type` `object_type` varchar(1) NOT NULL DEFAULT 'O';
ALTER TABLE `?:settings_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:settings_objects` ADD KEY `name` (`name`);
ALTER TABLE `?:shipping_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:shipping_service_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:shippings` CHANGE `params` `service_params` text NOT NULL;
ALTER TABLE `?:state_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:static_data_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:status_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:statuses` DROP PRIMARY KEY;
ALTER TABLE `?:statuses` ADD `status_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY;
ALTER TABLE `?:statuses` ADD UNIQUE KEY `status` (`status`,`type`);
ALTER TABLE `?:storage_data` CHANGE `data` `data` mediumblob NOT NULL;
ALTER TABLE `?:tax_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:tax_rates` DROP `apply_to`;
DROP TABLE IF EXISTS `?:theme_presets`;
CREATE TABLE `?:theme_presets` (  `preset_id` int(11) NOT NULL AUTO_INCREMENT,  `data` mediumblob NOT NULL,  `name` varchar(64) NOT NULL DEFAULT '',  `theme` varchar(64) NOT NULL DEFAULT 'basic',  `is_default` tinyint(4) NOT NULL DEFAULT '0',  PRIMARY KEY (`preset_id`),  KEY `is_default` (`is_default`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `?:ult_language_values` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:ult_product_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:ult_product_descriptions` ADD `promo_text` mediumtext NOT NULL;
DROP TABLE IF EXISTS `?:ult_status_descriptions`;
CREATE TABLE `?:ult_status_descriptions` (  `company_id` int(11) unsigned NOT NULL,  `status` char(1) NOT NULL DEFAULT '',  `type` char(1) NOT NULL DEFAULT 'O',  `email_subj` varchar(255) NOT NULL DEFAULT '',  `email_header` text NOT NULL,  `lang_code` char(2) NOT NULL DEFAULT '',  PRIMARY KEY (`status`,`type`,`lang_code`,`company_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `?:user_profiles` DROP `b_title`;
ALTER TABLE `?:user_profiles` DROP `s_title`;
ALTER TABLE `?:usergroup_descriptions` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:usergroups` ADD `company_id` int(11) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `?:users` DROP KEY `uname`;
ALTER TABLE `?:users` DROP `title`;
ALTER TABLE `?:users` CHANGE `lang_code` `lang_code` char(2) NOT NULL DEFAULT '';
ALTER TABLE `?:users` ADD `api_key` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `?:users` ADD KEY `uname` (`firstname`,`lastname`);
ALTER TABLE `?:ult_objects_sharing` CHANGE `share_object_id` `share_object_id` mediumint(8) NOT NULL default 0;
DROP TABLE IF EXISTS `?:bm_grids_descriptions`;



INSERT INTO `?:settings_sections` (section_id, parent_id, edition_type, name, position, type) VALUES ('92', '91', 'ROOT', 'general', '0', 'TAB') ON DUPLICATE KEY UPDATE `section_id` = `section_id`;
INSERT INTO `?:settings_variants` (variant_id, object_id, name, position) VALUES ('169', '64', 'null', '30') ON DUPLICATE KEY UPDATE `variant_id` = `variant_id`;

UPDATE `?:settings_sections` SET edition_type='ROOT,VENDOR' WHERE name IN ('General', 'Appearance', 'Sitemap', 'Thumbnails', 'Reports', 'Image_verification', 'Logging') AND type = 'CORE';
UPDATE `?:settings_sections` SET edition_type='ROOT' WHERE name IN ('Emails', 'Shippings', 'Upgrade_center', 'Security', 'Stores') AND type = 'CORE';
UPDATE `?:settings_sections` SET edition_type='ROOT,ULT:VENDOR' WHERE name IN ('Company') AND type = 'CORE';

UPDATE `?:settings_objects` SET edition_type='ROOT,VENDOR' WHERE name='log_type_news';
UPDATE `?:settings_objects` SET name='frontend_default_language', value = LOWER(value) WHERE name='customer_default_language';
UPDATE `?:settings_objects` SET name='backend_default_language', value = LOWER(value) WHERE name='admin_default_language';
UPDATE `?:settings_objects` SET position='53' WHERE name='order_start_id';
UPDATE `?:settings_objects` SET name='theme_name', value='basic' WHERE name='skin_name_customer';
UPDATE `?:settings_vendor_values` SET value='basic' WHERE object_id = (SELECT object_id FROM ?:settings_objects WHERE name = 'theme_name');
UPDATE `?:settings_objects` SET name='use_for_email_share', value='Y' WHERE name='use_for_send_to_friend';
UPDATE `?:settings_objects` SET name='init_addons', value='' WHERE name='translation_mode';
UPDATE `?:settings_objects` SET edition_type='ROOT,VENDOR' WHERE name='log_type_orders';
UPDATE `?:settings_objects` SET edition_type='ROOT,VENDOR' WHERE name='log_type_users';
UPDATE `?:settings_objects` SET edition_type='ROOT,VENDOR' WHERE name='log_type_products';
UPDATE `?:settings_objects` SET edition_type='ROOT,VENDOR' WHERE name='log_type_categories';
UPDATE `?:settings_objects` SET edition_type='ROOT,VENDOR' WHERE name='log_type_database';
UPDATE `?:settings_objects` SET edition_type='ROOT,VENDOR' WHERE name='log_type_requests';
UPDATE `?:settings_objects` SET edition_type='ROOT,ULT:VENDOR', name='store_mode', section_id='2', section_tab_id='0', type='C', value='N', position='50' WHERE name='store_mode';
UPDATE `?:settings_objects` SET value='#M#products_multicolumns=Y&products_without_options=Y&short_list=Y' WHERE name='default_products_layout_templates';
UPDATE `?:settings_objects` SET value='280' WHERE name='product_details_thumbnail_width';
UPDATE `?:settings_objects` SET value='' WHERE name='product_details_thumbnail_height';
UPDATE `?:settings_objects` SET edition_type='ROOT', name='store_optimization_dev', section_id='2', section_tab_id='0', type='C', value='N', position='52', is_global='Y', handler='' WHERE name='store_optimization';
UPDATE `?:settings_objects` SET edition_type='ROOT,VENDOR' WHERE name='log_type_general';
UPDATE `?:settings_objects` SET name='enable_quick_view', value='Y' WHERE name='disable_quick_view';
UPDATE `?:settings_descriptions` SET value = 'Enable quick view' WHERE object_type = 'O' AND object_id = (SELECT object_id FROM `?:settings_objects` WHERE name = 'enable_quick_view');

UPDATE `?:settings_vendor_values` SET value = LOWER(value) WHERE object_id IN (SELECT object_id FROM `?:settings_objects` WHERE name = 'frontend_default_language');

INSERT INTO `?:settings_descriptions` (object_id, object_type, lang_code, value, tooltip) VALUES ((SELECT object_id FROM ?:settings_objects WHERE name = 'store_mode'), 'O', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'Close storefront', '');

DROP TABLE IF EXISTS ?:settings_vendor_values_upg;
CREATE TABLE `?:settings_vendor_values_upg` (
  `object_id` mediumint(8) unsigned NOT NULL auto_increment,
  `company_id` int(11) unsigned NOT NULL,
  `name` varchar(128) NOT NULL default '',
  `section_name` varchar(128) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`object_id`, `company_id`)
) Engine=MyISAM DEFAULT CHARSET UTF8;

INSERT INTO ?:settings_vendor_values_upg
    SELECT
        ?:settings_objects.object_id,
        ?:settings_vendor_values.company_id,
        ?:settings_objects.name,
        ?:settings_sections.name as section_name,
        ?:settings_vendor_values.value
    FROM ?:settings_objects
    LEFT JOIN ?:settings_sections ON ?:settings_sections.section_id = ?:settings_objects.section_id
    INNER JOIN ?:settings_vendor_values ON ?:settings_vendor_values.object_id = ?:settings_objects.object_id;

DELETE FROM ?:settings_vendor_values WHERE object_id IN (
    SELECT object_id FROM ?:settings_objects WHERE section_id IN (
        SELECT section_id FROM ?:settings_sections WHERE type = 'ADDON'
    )
);

DROP TABLE IF EXISTS ?:settings_objects_upg;
CREATE TABLE `?:settings_objects_upg` (
  `object_id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `section_name` varchar(128) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`object_id`)
) Engine=MyISAM DEFAULT CHARSET UTF8;

INSERT INTO ?:settings_objects_upg
    SELECT
        ?:settings_objects.object_id,
        ?:settings_objects.name,
        ?:settings_sections.name as section_name,
        ?:settings_objects.value
    FROM ?:settings_objects
    LEFT JOIN ?:settings_sections ON ?:settings_sections.section_id = ?:settings_objects.section_id;

DELETE FROM ?:settings_descriptions WHERE object_type = 'V' AND object_id IN (
    SELECT variant_id FROM ?:settings_variants WHERE object_id IN (
        SELECT object_id FROM ?:settings_objects WHERE section_id IN (
            SELECT section_id FROM ?:settings_sections WHERE type = 'ADDON'
        )
    )
);

DELETE FROM ?:settings_descriptions WHERE object_type = 'O' AND object_id IN (
    SELECT object_id FROM ?:settings_objects WHERE section_id IN (
        SELECT section_id FROM ?:settings_sections WHERE type = 'ADDON'
    )
);

DELETE FROM ?:settings_descriptions WHERE object_type = 'S' AND object_id IN (
    SELECT section_id FROM ?:settings_sections WHERE parent_id IN (
        SELECT section_id FROM ?:settings_sections WHERE type = 'ADDON'
    )
);

DELETE FROM ?:settings_descriptions WHERE object_type = 'S' AND object_id IN (
    SELECT section_id FROM ?:settings_sections WHERE type = 'ADDON'
);

DELETE FROM ?:settings_variants WHERE object_id IN (
    SELECT object_id FROM ?:settings_objects WHERE section_id IN (
        SELECT section_id FROM ?:settings_sections WHERE type = 'ADDON'
    )
);

DELETE FROM ?:settings_objects WHERE section_id IN (
    SELECT section_id FROM ?:settings_sections WHERE type = 'ADDON'
);

DELETE s1, s2 FROM ?:settings_sections s1 LEFT JOIN ?:settings_sections as s2 ON s2.parent_id = s1.section_id WHERE s1.type = 'ADDON';

UPDATE `?:payments` SET template = CONCAT('views/orders/components/payments/', template);
UPDATE `?:payment_processors` SET processor_template = CONCAT('views/orders/components/payments/', processor_template);

INSERT INTO `?:payment_processors` (processor, processor_script, processor_template, admin_template, callback, type) VALUES ('Rapid API', 'rapidapi.php', 'views/orders/components/payments/cc.tpl', 'rapidapi.tpl', 'N', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
INSERT INTO `?:payment_processors` (processor, processor_script, processor_template, admin_template, callback, type) VALUES ('PayPal Advanced', 'paypal_advanced.php', 'views/orders/components/payments/cc_outside.tpl', 'paypal_advanced.tpl', 'N', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;

INSERT INTO `?:settings_objects` (edition_type, name, section_id, section_tab_id, type, value, position, is_global, handler) VALUES ('ROOT', 'storage', '0', '0', 'I', 'a:1:{s:7:\"storage\";s:4:\"file\";}', '10', 'N', '');
INSERT INTO `?:settings_objects` (edition_type, name, section_id, section_tab_id, type, value, position, is_global, handler) VALUES ('ROOT,ULT:VENDOR', 'product_quick_view_thumbnail_width', '9', '0', 'U', '220', '135', 'Y', '');
INSERT INTO `?:settings_descriptions` (object_id, object_type, lang_code, value, tooltip) VALUES ((SELECT object_id FROM ?:settings_objects WHERE name = 'product_quick_view_thumbnail_width'), 'O', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'Product quick view thumbnail width', '');
INSERT INTO `?:settings_objects` (edition_type, name, section_id, section_tab_id, type, value, position, is_global, handler) VALUES ('ROOT,ULT:VENDOR', 'product_quick_view_thumbnail_height', '9', '0', 'U', '', '136', 'Y', '');
INSERT INTO `?:settings_descriptions` (object_id, object_type, lang_code, value, tooltip) VALUES ((SELECT object_id FROM ?:settings_objects WHERE name = 'product_quick_view_thumbnail_height'), 'O', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'Product quick view thumbnail height', '');
INSERT INTO `?:settings_objects` (edition_type, name, section_id, section_tab_id, type, value, position, is_global, handler) VALUES ('ROOT', 'current_timestamp', 0, 0, 'T', '{HD:TIMESTAMP}', 10, 'Y','');
INSERT INTO `?:settings_objects` (edition_type, name, section_id, section_tab_id, type, value, position, is_global, handler) VALUES ('ROOT', 'temando_enabled', '7', '0', 'C', 'N', '95', 'Y', '');
INSERT INTO `?:settings_descriptions` (object_id, object_type, lang_code, value, tooltip) VALUES ((SELECT object_id FROM ?:settings_objects WHERE name = 'temando_enabled'), 'O', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'Enable Temando', '');

INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54410_GENERALROAD', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: All Australian Logistics: General Road', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60005_AIREXPRESS-PRE-SCHEDULEDPICK-UPSONLY', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Air Express (BULK): Air Express - Pre-scheduled pick-ups only', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60005_1KGSATCHEL-SATCHELREQUIRED', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Air Express (BULK): 1Kg Satchel- Satchel required', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60005_3KGSATCHEL-SATCHELREQUIRED', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Air Express (BULK): 3Kg Satchel - Satchel Required', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60005_5KGSATCHEL-SATCHELREQUIRED', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Air Express (BULK): 5Kg Satchel - Satchel Required', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60006_AIREXPRESS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Air Express Adhoc: Air Express', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60006_1KGSATCHEL-SATCHELREQUIRED', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Air Express Adhoc: 1kg Satchel - Satchel Required', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60006_3KGSATCHEL-SATCHELREQUIRED', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Air Express Adhoc: 3kg Satchel - Satchel Required', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60006_5KGSATCHEL-SATCHELREQUIRED', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Air Express Adhoc: 5kg Satchel - Satchel Required', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54381_ROADEXPRESS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Express: Road Express', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54381_ROADEXPRESS-CALLBEFOREDELIVERYSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Express: Road Express - Call Before Delivery Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54426_ROADEXPRESS-PRE-SCHEDULEDPICK-UPSONLY', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Express (Bulk): Road Express- Pre-scheduled pick-ups only', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54426_ROADEXPRESS(BULK)CALLBEFOREDELIVERYSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Express (Bulk): Road Express (Bulk) Call Before Delivery Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60020_ROADEXPRESSPALLET1.4M(HEIGHTMAX)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Express Pallets: Road Express Pallet 1.4m ( height max)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60020_ROADEXPRESSPALLET2.4M(HEIGHTMAX)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Express Pallets: Road Express Pallet 2.4m (height max)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60008_ALLIEDCOURIERSYDNEY-SAMEDAYSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Courier Sydney: Allied Courier Sydney- Same Day Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60010_ALLIEDCOURIERBRISBANE-SAMEDAYSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Courier Brisbane: Allied Courier Brisbane- Same Day Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60012_ALLIEDCOURIERMELBORNE-SAME-DAYSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Courier Melbourne: Allied Courier Melborne- Same-Day Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60014_ALLIEDCOURIERADELAIDE-SAMEDAYSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Courier Adelaide: Allied Courier Adelaide- Same Day Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60016_ALLIEDCOURIERPERTH-SAMEDAYSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Allied Courier Perth: Allied Courier Perth- Same Day Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EPARCEL-STANDARD', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): eParcel - Standard', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EPARCELS4', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): eParcel S4', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EPARCELS5', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): eParcel S5', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EPARCELS6-ATL-AUTHORITYTOLEAVE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): eParcel S6 - ATL - Authority to Leave', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EPARCEL-STANDARDS9', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): eParcel - Standard S9', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EPARCEL-EXPRESSX1', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): eParcel- Express X1', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EPARCEL-EXPRESSX2', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): eParcel - Express X2', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EPARCEL-EXPRESSX6', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): eParcel - Express X6', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EPARCELEXPRESSX5', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): eParcel Express X5', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EXPRESSEPARCELX5-AUTHORITYTOLEAVE(ATL)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): Express eParcel X5- Authority to Leave (ATL)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EXPRESSEPARCEL-SATCHELFLATRATEUPTO5KG', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): Express eParcel - Satchel Flat Rate Up to 5Kg', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54440_EPARCEL-WINECARTONS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Australia Post (eParcel): eParcel- Wine Cartons', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54361_EXPRESS(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Autotrans: Express (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54361_GENERAL(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Autotrans: General (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54429_1MANTAILGATE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Bluestar Logistics Bulk: 1 Man Tailgate', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54429_2MANTAILGATE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Bluestar Logistics Bulk: 2 Man Tailgate', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54429_ROADEXPRESS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Bluestar Logistics Bulk: Road Express', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54429_PERPALLETRATES', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Bluestar Logistics Bulk: Per Pallet Rates', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54384_EXPRESS(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Bohaul Express (locked): Express (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54384_GENERAL(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Bohaul Express (locked): General (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60018_TRUCKLOAD-BDOUBLE34PALLETMAX(2.4MHIGHMAX))', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Bulk Transport: Truckload- B double 34 pallet max (2.4m high max))', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60018_TRUCKLOAD-SINGLE22PALLETMAX,(2.4HIGHMAX)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Bulk Transport: Truckload- Single 22pallet max, (2.4 high Max)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60018_TAILGATELIFTREQUIRED', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Bulk Transport: Tailgate Lift Required', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60018_EXPRESS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Bulk Transport: Express', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54380_EXPRESS(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Capital Transport: Express (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54380_GENERAL(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Capital Transport: General (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54433_SAMEDAY', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Capital Transport Courier: same day', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54432_HOMEDELIVERYSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Capital Transport HDS: Home Delivery Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54356_GENERAL(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Ceva Logistics: General (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54371_SAMEDAYTRUCK<1000KGS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Civic Transport Services: Sameday Truck <1000kgs', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54371_SAMEDAYCOURIER<25KGS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Civic Transport Services: Sameday Courier <25kgs', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54371_SAMEDAYUTE/VAN<500KGS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Civic Transport Services: Sameday Ute/Van <500kgs', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54391_PALLETS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Concord Park: Pallets', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54453_STANDARD1MANSERVICE(PERMPICKUPSONLY)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Cope Sensitive Freight: Standard 1 Man service (perm pickups only)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54453_1MANTAILGATESERVICE(PERMPICKUPONLY)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Cope Sensitive Freight: 1 Man Tailgate Service (Perm pick up ONLY)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54425_1KGPARCEL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Couriers Please: 1 Kg Parcel', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54425_SATCHEL1KG(MAXWEIGHT)SATCHELREQUIRED', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Couriers Please: Satchel 1kg (max weight) satchel required', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54425_3KGPARCEL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Couriers Please: 3Kg Parcel', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54425_SATCHEL3KG(MAXWEIGHT)SATCHELREQUIRED', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Couriers Please: Satchel 3kg (max weight) satchel required', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54425_5KGPARCEL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Couriers Please: 5Kg Parcel', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54425_SATCHEL5KG(MAXWEIGHT)SATCHELREQUIRED', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Couriers Please: Satchel 5kg (max weight) satchel required', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60046_ROADSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Couriers Please Parcel: Road Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60052_ROADSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Couriers Please Parcel Metro: Road Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60053_DIRECTCOURIERS1-2HOURSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Direct Couriers: Direct Couriers 1- 2 Hour Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60053_DIRECTCOURIERS3-4HOURSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Direct Couriers: Direct Couriers 3-4 Hour Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60053_DIRECTCOURIERSASAPSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Direct Couriers: Direct Couriers ASAP Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60061_LOCALDELIVERY(BULKBREAK)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Direct Couriers (Bulk): Local Delivery (Bulk Break)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54377_GENERAL(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Door 2 Door Car Carrying: General (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54448_ROAD', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Door to Door Car Carrying: Road', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54444_A5/1KG-SATCHEL-SATCHELREQUIRED:CONTACTUS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers A5SAT: A5/1Kg-Satchel - SATCHEL REQUIRED: CONTACT US', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54427_METROPARCEL/CARTON', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Adhoc: Metro Parcel/ Carton', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54427_PARCEL/CARTON(GR)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Adhoc: Parcel/ Carton (Gr)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54427_SHORTHAULPARCEL/CARTON', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Adhoc: Short Haul Parcel/ Carton', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54427_PARCEL/CARTON(OR)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Adhoc: Parcel/ Carton (Or)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54427_MEDIUMHAULPARCEL/CARTON', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Adhoc: Medium Haul Parcel/ Carton', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54427_PARCEL/CARTON(R)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Adhoc: Parcel/ Carton (R)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54427_A2/5KGSATCHEL--SATCHELREQUIRED:CONTACTUS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Adhoc: A2/5KG Satchel--SATCHEL REQUIRED: CONTACT US', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54427_A3/3KGSATCHEL--SATCHELREQUIRED:CONTACTUS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Adhoc: A3/3KG Satchel--SATCHEL REQUIRED: CONTACT US', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54428_METROPARCEL/CARTON', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Bulk: Metro Parcel/ Carton', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54428_DEPOTTODOOR-BULKBREAK', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Bulk: Depot to Door - Bulk Break', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54428_DEPOTTODOOR-PERITEM', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Bulk: Depot to Door - Per Item', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54428_DEPOTTODOOR-SHORTHAUL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Fastway Couriers Bulk: Depot to Door - Shorthaul', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54329_GENERAL(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Followmont Transport: General (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60023_EXPRESS3HOUR(SAMEDAYDELIVERY)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: GoParcel: Express 3 Hour (Same Day Delivery)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60048_EXPRESS3HOUR(SAMEDAYDELIVERY)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: GoParcel (BULK): Express 3 Hour (Same Day Delivery)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54398_BULKSERVICE-PRE-SCHEDULEDPICKUPSONLY!', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Hunter Express (bulk): Bulk Service - Pre-Scheduled Pick Ups Only!', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54344_GENERAL(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Hunter Express.: General (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_1TTRAY/ROOFRACKS(250KG)-2HOURTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: 1T Tray/ Roof Racks (250Kg) - 2 Hour Turn Around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_1TONNECOUIER-2HOURTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: 1 Tonne Couier - 2 Hour Turn Around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_EXPRESSCOURIER-2HRTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: Express Courier - 2 Hr Turn Around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_EXPRESSCOURIERBIKE-1.5HOURSTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: Express Courier Bike - 1.5 hours Turn Around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_STANDARDCOURIER-4HRTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: Standard Courier - 4hr Turn Around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_STANDARDCOURIERBIKE-4HOURS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: Standard Courier Bike - 4 Hours', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_COURIERVAN/TRAY-2HOURTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: Courier Van/Tray - 2Hour Turn Around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_VIP1TONNECOURIER-PRIORITYALLOCATIOIN,DIRECTRU', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: VIP 1Tonne Courier-Priority Allocatioin, Direct Ru', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_1TTRAY/ROOFRACKS(250KG)PRIORITYALLOC,DIRECTRUN', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: 1T Tray/Roof Racks(250Kg)Priority Alloc,Direct Run', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_VIPCOURIER-PRIORITYALLOCATION,DIRECTRUN', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: VIP Courier - Priority Allocation, Direct Run', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_VIPCOURIERBIKE-DIRECTURGENT', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: VIP Courier Bike - Direct Urgent', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54379_VIPVAN/TRAY-PRIORITYALLOCATION,DIRECTRUN', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Brisbane: VIP Van/Tray - Priority Allocation, Direct Run', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54457_1TONNECOUIER-3HOURTURN', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Melbourne - Same Day Service: 1 Tonne Couier - 3 Hour Turn ', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54457_1TTRAY/ROOFRACKS(250KG)-3HOURTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Melbourne - Same Day Service: 1T Tray/ Roof Racks (250Kg) - 3 Hour Turn Around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54457_EXPRESSCOURIER-2HRTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Melbourne - Same Day Service: Express Courier - 2 Hr Turn Around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54457_STANDARDCOURIER-4HRS(2HRSCBD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Melbourne - Same Day Service: Standard Courier -4 hrs (2hrs CBD)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54457_COURIERVAN/TRAY-2HOURTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Melbourne - Same Day Service: Courier Van/Tray - 2Hour Turn Around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54457_VIP1TONNECOURIER-PRIORITYALLOCATIOIN,DIRECTRU', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Melbourne - Same Day Service: VIP 1Tonne Courier-Priority Allocatioin, Direct Ru', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54457_1TTRAY/ROOFRACKS(250KG)PRIORITYALLOC,DIRECTRUN', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Melbourne - Same Day Service: 1T Tray/Roof Racks(250Kg)Priority Alloc,Direct Run', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54457_VIPCOURIER-PRIORITYALLOCATION,DIRECTRUN', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Melbourne - Same Day Service: VIP Courier - Priority Allocation, Direct Run', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54457_VIPVAN/TRAY-PRIORITYALLOCATION,DIRECTRUN', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Melbourne - Same Day Service: VIP Van/Tray - Priority Allocation, Direct Run', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54458_1TONNECOURIER-2HOURTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Sydney: 1 Tonne Courier -2 Hour Turn around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54458_STANDARDCOURIER-2HOURTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Sydney: Standard Courier - 2 hour turn around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54458_STANDARDVANORTRAY-2HOURTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Sydney: Standard Van or Tray - 2 hour turn around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54458_WAGONVANTRAYCOURIER-2HOURTURNAROUND', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Kings Transport Sydney: Wagon Van Tray Courier - 2 hour turn around', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54358_GENERAL(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Mainfreight: General (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54417_REGIONAL1MANTAILGATE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Neway Transport (NT): Regional 1 Man Tailgate', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54417_REGIONAL2MANTAILGATE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Neway Transport (NT): Regional 2 man tailgate', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54417_TAILGATESERVICE(2MEN)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Neway Transport (NT):  Tailgate Service (2 Men)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54417_TAILGATESENSITIVE(1MAN)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Neway Transport (NT): Tailgate Sensitive (1 Man)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54357_GENERALRAILSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: SCT Logistics: General Rail Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54357_GENERALROADSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: SCT Logistics: General Road Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54353_GENERAL(ROAD)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: SMB Car Transport: General (Road)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54359_EXPRESSPREMIUM(ETAMETROONLY)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Startrack: Express Premium (eta metro only)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54359_EXPRESSPREMIUMAAESATCHEL(ETAMETROONLY)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Startrack: Express Premium AAE Satchel (eta metro only)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54396_EXPRESSPREMIUM(ETAMETROONLY)-AUTHTOLEAVE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Startrack ATL: Express Premium (eta metro only) - Auth to Leave', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54396_EXPRESSPREMIUMSATCHEL(ETAISMETRO)-ATL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Startrack ATL: Express Premium Satchel (eta is metro) - ATL', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54405_NEXTFLIGHT:EXTRACOSTSAPPLYFORREGIONALAREAS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Startrack Next Flight: Next Flight: Extra Costs Apply for Regional Areas', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54414_URGENTDIRECTSERVICE(NEXTAVAILALBLEVEHICLE)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Sunexpress Transport Solutions: Urgent Direct Service (Next Availalble Vehicle)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54414_OVERNIGHTSERVICE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Sunexpress Transport Solutions: Overnight Service', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54414_SAMEDAYSERVICE(ALLOW5HRSFORDELIVERY)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Sunexpress Transport Solutions: Same Day Service (allow 5 hrs for delivery)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54414_SAMEDAYSERVICE(ALLOW3HRSFORDELIVERY)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: Sunexpress Transport Solutions: Same Day Service (allow 3hrs for delivery)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54397_OVERNIGHTEXPRESS(AIR)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT: Overnight Express (AIR)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54397_DELIVERYBEFORE9AMNEXTDAY(AIR)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT: Delivery Before 9AM Next Day (AIR)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54397_DELIVERYBEFORE10AMNEXTDAY(AIR)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT: Delivery Before 10AM Next Day (AIR)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54397_DELIVERYBEFORENOONNEXTDAY(AIR)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT: Delivery before Noon Next Day (AIR)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60027_DELIVERYBEFORE10AMNEXTDAY(AIR)-ATL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT ATL: Delivery Before 10AM Next Day (AIR)- ATL', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60027_DELIVERYBEFORENOONNEXTDAY(AIR)-ATL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT ATL: Delivery before Noon Next Day (AIR) - ATL', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60027_DELIVERYBEFORE9AMNEXTDAY-(AIR)-ATL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT ATL: Delivery Before 9AM Next Day- (AIR) - ATL', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60027_OVERNIGHTEXPRESS(AIR)-AUTHTOLEAVE(ATL)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT ATL: Overnight Express (AIR) - Auth to Leave (ATL)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60032_DELIVERYBEFORE10AMNEXTDAY(AIR)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Satchel Only: Delivery Before 10AM Next Day (AIR)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60032_DELIVERYBEFORE9AMNEXTDAY(AIR)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Satchel Only: Delivery Before 9AM Next Day (AIR)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60032_DELIVERYBEFORENOONNEXTDAY(AIR)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Satchel Only: Delivery before Noon Next Day (AIR)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60032_OVERNIGHTEXPRESS(AIR)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Satchel Only: Overnight Express (AIR)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60037_DELIVERYBEFORE10AMNEXTDAY(AIR)ATL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Satchel Only ATL: Delivery Before 10AM Next Day (AIR) ATL', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60037_DELIVERYBEFORENOONNEXTDAY(AIR)ATL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Satchel Only ATL: Delivery before Noon Next Day (AIR) ATL', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60037_DELIVERYBEFORE9AMNEXTDAY(AIR)ATL', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Satchel Only ATL: Delivery Before 9AM Next Day (AIR) ATL', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60037_OVERNIGHTEXPRESS(AIR)AUTHORITYTOLEAVE(ATL)', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Satchel Only ATL: Overnight Express (AIR) Authority to Leave (ATL)', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54421_ROADEXPRESS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Sensitive: Road Express', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54421_OVERNIGHTEXPRESS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Sensitive: Overnight Express', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '54421_SAMEDAYTIMECRITICALNATIONWIDE', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Sensitive: Sameday Time Critical Nationwide', 'en');
INSERT INTO `?:shipping_services` (status, module, carrier, code, sp_file) VALUES ('A', 'temando', 'TMD', '60089_ROADEXPRESS', '');
INSERT INTO `?:shipping_service_descriptions` (service_id, description, lang_code) VALUES ((SELECT LAST_INSERT_ID()), 'Temando: TNT Road: Road Express', 'en');

DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (SELECT service_id FROM ?:shipping_services WHERE module = 'intershipper');
DELETE FROM ?:shipping_services WHERE module = 'intershipper';

UPDATE ?:shipping_service_descriptions SET description = 'USPS First-Class Package International Service' WHERE service_id = 204;

UPDATE `?:shipping_service_descriptions` SET lang_code = (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language') WHERE service_id IN (SELECT service_id FROM ?:shipping_services WHERE module = 'temando');

REPLACE INTO `?:countries` (code, code_A3, code_N3, region, lat, lon, status) VALUES ('CW', 'CUW', '531', 'LA', '12.18', '-69', 'A');
REPLACE INTO `?:countries` (code, code_A3, code_N3, region, lat, lon, status) VALUES ('SX', 'SXM', '534', 'LA', '18.07', '-63.05', 'A');

INSERT INTO `?:theme_presets` (preset_id, data, name, theme, is_default) VALUES ('1', 'a:4:{s:7:\"general\";a:1:{s:15:\"rounded_corners\";s:1:\"1\";}s:6:\"colors\";a:13:{s:5:\"links\";s:7:\"#0088cc\";s:4:\"menu\";s:7:\"#5f5f5f\";s:4:\"base\";s:7:\"#808080\";s:4:\"font\";s:7:\"#333333\";s:14:\"primary_button\";s:7:\"#fb8913\";s:16:\"secondary_button\";s:7:\"#d9d9d9\";s:7:\"sidebar\";s:7:\"#a4a4a4\";s:5:\"price\";s:7:\"#a80006\";s:14:\"discount_label\";s:7:\"#62ad00\";s:8:\"in_stock\";s:7:\"#62ad00\";s:12:\"out_of_stock\";s:7:\"#a80006\";s:11:\"footer_text\";s:7:\"#808080\";s:10:\"decorative\";s:7:\"#e3e3e3\";}s:5:\"fonts\";a:5:{s:9:\"body_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"13\";}s:13:\"headings_font\";a:3:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"26\";s:5:\"style\";a:1:{s:1:\"B\";s:1:\"1\";}}s:10:\"links_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"13\";}s:10:\"price_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"36\";}s:12:\"buttons_font\";a:3:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"11\";s:5:\"style\";a:1:{s:1:\"B\";s:1:\"1\";}}}s:11:\"backgrounds\";a:5:{s:10:\"general_bg\";a:4:{s:5:\"color\";s:7:\"#ffffff\";s:10:\"image_data\";s:0:\"\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:12:\"top_panel_bg\";a:5:{s:5:\"color\";s:7:\"#f3f3f3\";s:8:\"gradient\";s:7:\"#efefef\";s:10:\"full_width\";s:1:\"1\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:9:\"header_bg\";a:4:{s:5:\"color\";s:7:\"#ffffff\";s:8:\"gradient\";s:7:\"#ffffff\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:10:\"content_bg\";a:3:{s:5:\"color\";s:7:\"#ffffff\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:9:\"footer_bg\";a:5:{s:5:\"color\";s:7:\"#f3f3f3\";s:8:\"gradient\";s:7:\"#f3f3f3\";s:10:\"full_width\";s:1:\"1\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}}}', 'Default', 'basic', '1') ON DUPLICATE KEY UPDATE `preset_id` = `preset_id`;
INSERT INTO `?:theme_presets` (preset_id, data, name, theme, is_default) VALUES ('2', 'a:4:{s:7:\"general\";a:1:{s:15:\"rounded_corners\";s:1:\"0\";}s:6:\"colors\";a:13:{s:5:\"links\";s:7:\"#00a4e2\";s:4:\"menu\";s:7:\"#43484b\";s:4:\"base\";s:7:\"#808080\";s:4:\"font\";s:7:\"#333333\";s:14:\"primary_button\";s:7:\"#37c7f7\";s:16:\"secondary_button\";s:7:\"#cccccc\";s:7:\"sidebar\";s:7:\"#46b9da\";s:5:\"price\";s:7:\"#f06900\";s:14:\"discount_label\";s:7:\"#62ad00\";s:8:\"in_stock\";s:7:\"#62ad00\";s:12:\"out_of_stock\";s:7:\"#a80006\";s:11:\"footer_text\";s:7:\"#efefef\";s:10:\"decorative\";s:7:\"#e3e3e3\";}s:5:\"fonts\";a:5:{s:9:\"body_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"13\";}s:13:\"headings_font\";a:3:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"26\";s:5:\"style\";a:1:{s:1:\"B\";s:1:\"1\";}}s:10:\"links_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"13\";}s:10:\"price_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"36\";}s:12:\"buttons_font\";a:3:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"11\";s:5:\"style\";a:1:{s:1:\"B\";s:1:\"1\";}}}s:11:\"backgrounds\";a:5:{s:10:\"general_bg\";a:4:{s:5:\"color\";s:7:\"#ffffff\";s:10:\"image_data\";s:0:\"\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:12:\"top_panel_bg\";a:5:{s:5:\"color\";s:7:\"#46b9da\";s:8:\"gradient\";s:7:\"#46b9da\";s:10:\"full_width\";s:1:\"1\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:9:\"header_bg\";a:4:{s:5:\"color\";s:7:\"#ffffff\";s:8:\"gradient\";s:7:\"#ffffff\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:10:\"content_bg\";a:3:{s:5:\"color\";s:7:\"#ffffff\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:9:\"footer_bg\";a:5:{s:5:\"color\";s:7:\"#5f5f5f\";s:8:\"gradient\";s:7:\"#5f5f5f\";s:10:\"full_width\";s:1:\"1\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}}}', 'Ocean', 'basic', '0') ON DUPLICATE KEY UPDATE `preset_id` = `preset_id`;
INSERT INTO `?:theme_presets` (preset_id, data, name, theme, is_default) VALUES ('4', 'a:4:{s:7:\"general\";a:1:{s:15:\"rounded_corners\";s:1:\"1\";}s:6:\"colors\";a:13:{s:5:\"links\";s:7:\"#0088cc\";s:4:\"menu\";s:7:\"#5f5f5f\";s:4:\"base\";s:7:\"#808080\";s:4:\"font\";s:7:\"#333333\";s:14:\"primary_button\";s:7:\"#fb8913\";s:16:\"secondary_button\";s:7:\"#d9d9d9\";s:7:\"sidebar\";s:7:\"#a4a4a4\";s:5:\"price\";s:7:\"#a80006\";s:14:\"discount_label\";s:7:\"#62ad00\";s:8:\"in_stock\";s:7:\"#62ad00\";s:12:\"out_of_stock\";s:7:\"#a80006\";s:11:\"footer_text\";s:7:\"#808080\";s:10:\"decorative\";s:7:\"#e3e3e3\";}s:5:\"fonts\";a:5:{s:9:\"body_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"13\";}s:13:\"headings_font\";a:3:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"26\";s:5:\"style\";a:1:{s:1:\"B\";s:1:\"1\";}}s:10:\"links_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"13\";}s:10:\"price_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"36\";}s:12:\"buttons_font\";a:3:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"11\";s:5:\"style\";a:1:{s:1:\"B\";s:1:\"1\";}}}s:11:\"backgrounds\";a:5:{s:10:\"general_bg\";a:4:{s:5:\"color\";s:7:\"#ffffff\";s:10:\"image_data\";s:0:\"\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:12:\"top_panel_bg\";a:5:{s:5:\"color\";s:7:\"#f3f3f3\";s:8:\"gradient\";s:7:\"#efefef\";s:10:\"full_width\";s:1:\"1\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:9:\"header_bg\";a:4:{s:5:\"color\";s:7:\"#ffffff\";s:8:\"gradient\";s:7:\"#ffffff\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:10:\"content_bg\";a:3:{s:5:\"color\";s:7:\"#ffffff\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:9:\"footer_bg\";a:5:{s:5:\"color\";s:7:\"#f3f3f3\";s:8:\"gradient\";s:7:\"#f3f3f3\";s:10:\"full_width\";s:1:\"1\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}}}', 'Default', 'basic', '0') ON DUPLICATE KEY UPDATE `preset_id` = `preset_id`;
INSERT INTO `?:theme_presets` (preset_id, data, name, theme, is_default) VALUES ('5', 'a:4:{s:7:\"general\";a:1:{s:15:\"rounded_corners\";s:1:\"0\";}s:6:\"colors\";a:13:{s:5:\"links\";s:7:\"#00a4e2\";s:4:\"menu\";s:7:\"#43484b\";s:4:\"base\";s:7:\"#808080\";s:4:\"font\";s:7:\"#333333\";s:14:\"primary_button\";s:7:\"#37c7f7\";s:16:\"secondary_button\";s:7:\"#cccccc\";s:7:\"sidebar\";s:7:\"#46b9da\";s:5:\"price\";s:7:\"#f06900\";s:14:\"discount_label\";s:7:\"#62ad00\";s:8:\"in_stock\";s:7:\"#62ad00\";s:12:\"out_of_stock\";s:7:\"#a80006\";s:11:\"footer_text\";s:7:\"#efefef\";s:10:\"decorative\";s:7:\"#e3e3e3\";}s:5:\"fonts\";a:5:{s:9:\"body_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"13\";}s:13:\"headings_font\";a:3:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"26\";s:5:\"style\";a:1:{s:1:\"B\";s:1:\"1\";}}s:10:\"links_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"13\";}s:10:\"price_font\";a:2:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"36\";}s:12:\"buttons_font\";a:3:{s:6:\"family\";s:26:\"Arial,Helvetica,sans-serif\";s:4:\"size\";s:2:\"11\";s:5:\"style\";a:1:{s:1:\"B\";s:1:\"1\";}}}s:11:\"backgrounds\";a:5:{s:10:\"general_bg\";a:4:{s:5:\"color\";s:7:\"#ffffff\";s:10:\"image_data\";s:0:\"\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:12:\"top_panel_bg\";a:5:{s:5:\"color\";s:7:\"#46b9da\";s:8:\"gradient\";s:7:\"#46b9da\";s:10:\"full_width\";s:1:\"1\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:9:\"header_bg\";a:4:{s:5:\"color\";s:7:\"#ffffff\";s:8:\"gradient\";s:7:\"#ffffff\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:10:\"content_bg\";a:3:{s:5:\"color\";s:7:\"#ffffff\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}s:9:\"footer_bg\";a:5:{s:5:\"color\";s:7:\"#5f5f5f\";s:8:\"gradient\";s:7:\"#5f5f5f\";s:10:\"full_width\";s:1:\"1\";s:6:\"repeat\";s:6:\"repeat\";s:10:\"attachment\";s:6:\"scroll\";}}}', 'Ocean', 'basic', '0') ON DUPLICATE KEY UPDATE `preset_id` = `preset_id`;
DELETE FROM `?:payment_descriptions` WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM `?:payment_processors` WHERE processor_script IN ('cardia.php', '365.php', 'e_gold.php', 'winbank.php', 'camtech_direct.php')));
DELETE FROM `?:payments` WHERE processor_id IN (SELECT processor_id FROM `?:payment_processors` WHERE processor_script IN ('cardia.php', '365.php', 'e_gold.php', 'winbank.php', 'camtech_direct.php'));
DELETE FROM `?:payment_processors` WHERE processor_script IN ('cardia.php', '365.php', 'e_gold.php', 'winbank.php', 'camtech_direct.php');
DELETE FROM `?:status_data` WHERE status='B' AND type='O' AND param='calculate_for_payouts';
DELETE FROM `?:status_data` WHERE status='I' AND type='O' AND param='calculate_for_payouts';
DELETE FROM `?:status_data` WHERE status='C' AND type='O' AND param='calculate_for_payouts';
DELETE FROM `?:status_data` WHERE status='D' AND type='O' AND param='calculate_for_payouts';
DELETE FROM `?:status_data` WHERE status='F' AND type='O' AND param='calculate_for_payouts';
DELETE FROM `?:status_data` WHERE status='O' AND type='O' AND param='calculate_for_payouts';
DELETE FROM `?:status_data` WHERE status='P' AND type='O' AND param='calculate_for_payouts';
DELETE FROM `?:settings_descriptions` WHERE object_type = 'O' AND object_id IN (SELECT object_id FROM `?:settings_objects` WHERE name IN ('show_quick_menu', 'skin_name_admin', 'customer_ajax_based_pagination', 'ajax_add_to_cart', 'ajax_comparison_list', 'lh_visitor_data_last_clean', 'header_7018', 'use_for_apply_for_vendor_account'));
DELETE FROM `?:settings_objects` WHERE name IN ('show_quick_menu', 'skin_name_admin', 'customer_ajax_based_pagination', 'ajax_add_to_cart', 'ajax_comparison_list', 'lh_visitor_data_last_clean', 'header_7018', 'use_for_apply_for_vendor_account');
DELETE FROM `?:settings_descriptions` WHERE object_type = 'S' AND object_id = (SELECT section_id FROM `?:settings_sections` WHERE name='DHTML');
DELETE FROM `?:settings_sections` WHERE name='DHTML';
DELETE FROM `?:privileges` WHERE privilege='select_skins';
DELETE FROM `?:privileges` WHERE privilege='open_close_store';
DELETE FROM `?:privileges` WHERE privilege='manage_livehelp';
DELETE FROM `?:countries` WHERE code='AN';

UPDATE ?:destination_elements SET element = 'CW' WHERE element = 'AN';
UPDATE ?:languages SET country_code = 'CW' WHERE country_code = 'AN';
UPDATE ?:states SET country_code = 'CW' WHERE country_code = 'AN';
UPDATE ?:user_profiles SET b_country = 'CW' WHERE b_country = 'AN';
UPDATE ?:user_profiles SET s_country = 'CW' WHERE s_country = 'AN';
UPDATE ?:companies SET country = 'CW' WHERE country = 'AN';
UPDATE ?:orders SET b_country = 'CW' WHERE b_country = 'AN';
UPDATE ?:orders SET s_country = 'CW' WHERE s_country = 'AN';
UPDATE ?:settings_objects SET value = 'CW' WHERE value = 'AN' AND name = 'default_country';
UPDATE ?:settings_objects SET value = 'CW' WHERE value = 'AN' AND name = 'company_country';

DELETE FROM `?:privilege_descriptions` WHERE privilege='select_skins';
DELETE FROM `?:privilege_descriptions` WHERE privilege='open_close_store';
DELETE FROM `?:privilege_descriptions` WHERE privilege='manage_livehelp';
DELETE FROM `?:country_descriptions` WHERE code='AN';
REPLACE INTO ?:country_descriptions (code, lang_code, country) VALUES ('CW', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'Curaçao');
REPLACE INTO ?:country_descriptions (code, lang_code, country) VALUES ('SX', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'Sint Maarten');
INSERT INTO ?:destination_elements (destination_id, element, element_type) VALUES ('1', 'SX', 'C');

UPDATE `?:order_data` SET `type`='J' WHERE `type`='A' AND `data` LIKE '%partner_id%';
UPDATE `?:order_data` SET `type`='K' WHERE `type`='A';

DELETE FROM ?:ult_objects_sharing WHERE share_object_id IN (SELECT param_id FROM ?:static_data WHERE section IN ('C','T')) AND share_object_type = 'static_data';
DELETE FROM ?:static_data, ?:static_data_descriptions USING ?:static_data LEFT JOIN ?:static_data_descriptions ON ?:static_data.param_id = ?:static_data_descriptions.param_id WHERE ?:static_data.section IN ('C', 'T');

DELETE FROM ?:usergroup_privileges WHERE privilege = 'select_skins';
INSERT INTO ?:usergroup_privileges (usergroup_id, privilege) VALUES ('4', 'manage_design'), ('4', 'manage_storage'), ('4', 'manage_themes'), ('4', 'manage_translation');

UPDATE ?:addons SET separate = 0;
INSERT INTO ?:exim_layouts (name, cols, pattern_id, active) VALUES
('general', 'Feature name,Feature ID,Language,Type,Group,Description,Categories,Show on the features tab,Show in product list,Show in product header,Position,Status,Store', 'features', 'Y'),
('general', 'Variant,Variant ID,Language,Feature name,Feature group,Position', 'feature_variants', 'Y');

TRUNCATE TABLE ?:quick_menu;
INSERT INTO ?:quick_menu (`menu_id`, `user_id`, `url`, `parent_id`, `position`) VALUES ('1', '1', '', '0', '1'),
('2', '1', '', '0', '2'),
('3', '1', '', '0', '3'),
('4', '1', '', '0', '4'),
('5', '1', '', '0', '5'),
('6', '1', 'products.add', '1', '1'),
('7', '1', 'categories.add', '1', '2'),
('8', '1', 'order_management.new', '1', '3'),
('9', '1', 'pages.add?page_type=T', '1', '4'),
('10', '1', 'profiles.add', '1', '5'),
('11', '1', 'promotions.add', '1', '6'),
('12', '1', 'banners.add', '1', '7'),
('13', '1', 'orders.manage?view_id=1', '2', '1'),
('14', '1', 'orders.manage?view_id=2', '2', '2'),
('15', '1', 'orders.manage?view_id=3', '2', '3'),
('16', '1', 'orders.manage?view_id=4', '2', '4'),
('17', '1', 'block_manager.manage', '3', '1'),
('18', '1', 'menus.manage', '3', '2'),
('20', '1', 'template_editor.manage', '3', '4'),
('21', '1', 'shippings.manage', '4', '1'),
('22', '1', 'payments.manage', '4', '2'),
('23', '1', 'taxes.manage', '4', '3'),
('24', '1', 'products.manage', '4', '4'),
('25', '1', 'categories.manage', '4', '5'),
('26', '1', 'pages.manage', '4', '6'),
('27', '1', 'profiles.manage', '4', '7'),
('28', '1', 'banners.manage', '4', '8'),
('29', '1', 'settings.manage', '5', '1'),
('30', '1', 'database.manage', '5', '2'),
('31', '1', 'exim.import', '5', '3'),
('32', '1', 'languages.manage', '5', '4'),
('33', '1', 'statistics.reports?reports_group=system', '5', '5');
