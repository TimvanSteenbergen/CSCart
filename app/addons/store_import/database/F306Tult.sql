DELETE FROM `?:settings_descriptions` WHERE `object_id` = (SELECT `object_id` FROM `?:settings_objects` WHERE `name` = 'enable_suppliers') AND `object_type` = 'O';
DELETE FROM `?:settings_objects` WHERE `name` = 'enable_suppliers';
DELETE FROM `?:settings_descriptions` WHERE `object_id` = (SELECT `object_id` FROM `?:settings_objects` WHERE `name` = 'display_supplier') AND `object_type` = 'O';
DELETE FROM `?:settings_objects` WHERE `name` = 'display_supplier';
DELETE FROM `?:settings_descriptions` WHERE `object_id` = (SELECT `object_id` FROM `?:settings_objects` WHERE `name` = 'display_shipping_methods_separately') AND `object_type` = 'O';
DELETE FROM `?:settings_objects` WHERE `name` = 'display_shipping_methods_separately';

DELETE FROM `?:settings_sections` WHERE `name` = 'Suppliers';

DROP TABLE IF EXISTS ?:company_descriptions;
CREATE TABLE `?:company_descriptions` (
  `company_id` int(11) unsigned NOT NULL,
  `lang_code` char(2) NOT NULL,
  `company_description` text NOT NULL,
  PRIMARY KEY  (`company_id`,`lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO ?:status_data (status, type, param, value) VALUES ('B', 'O', 'calculate_for_payouts', 'N');
INSERT INTO ?:status_data (status, type, param, value) VALUES ('I', 'O', 'calculate_for_payouts', 'N');
INSERT INTO ?:status_data (status, type, param, value) VALUES ('C', 'O', 'calculate_for_payouts', 'Y');
INSERT INTO ?:status_data (status, type, param, value) VALUES ('D', 'O', 'calculate_for_payouts', 'N');
INSERT INTO ?:status_data (status, type, param, value) VALUES ('F', 'O', 'calculate_for_payouts', 'N');
INSERT INTO ?:status_data (status, type, param, value) VALUES ('O', 'O', 'calculate_for_payouts', 'Y');
INSERT INTO ?:status_data (status, type, param, value) VALUES ('P', 'O', 'calculate_for_payouts', 'Y');

INSERT INTO `?:settings_sections` (`parent_id`, `edition_type`, `name`, `position`, `type`) VALUES (0, 'ROOT', 'Stores', 0, 'CORE');
INSERT INTO `?:settings_descriptions` (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ((SELECT LAST_INSERT_ID()), 'S', (SELECT value FROM ?:settings_objects WHERE name = 'admin_default_language'), 'Stores', '');

/* Please use object_id for ULT from 2000 to 2999 */
REPLACE INTO `?:settings_objects` (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`) VALUES
 (2000, 'ROOT', 'share_users', (SELECT ?:settings_sections.section_id FROM ?:settings_sections WHERE ?:settings_sections.name = 'Stores'), 0, 'C', 'N', 10, 'Y');
REPLACE INTO `?:settings_descriptions` (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES (2000, 'O', 'EN', 'Share users among stores', '');
REPLACE INTO `?:settings_objects` (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`) VALUES
 (2001, 'ROOT', 'default_state_update_for_all', (SELECT ?:settings_sections.section_id FROM ?:settings_sections WHERE ?:settings_sections.name = 'Stores'), 0, 'S', 'not_active', 20, 'Y');
REPLACE INTO `?:settings_descriptions` (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES (2001, 'O', 'EN', 'Default state of the "Update for all stores" icon', '');
REPLACE INTO `?:settings_descriptions` (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES (2000, 'V', 'EN', 'Not Active', '');
REPLACE INTO `?:settings_descriptions` (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES (2001, 'V', 'EN', 'Active', '');

/* Please use variant_id for ULT from 2000 to 2999 */
REPLACE INTO `?:settings_variants` (`variant_id`, `object_id`, `name`, `position`) VALUES (2000, 2001, 'not_active', 1);
REPLACE INTO `?:settings_variants` (`variant_id`, `object_id`, `name`, `position`) VALUES (2001, 2001, 'active', 2);

REPLACE INTO `?:settings_objects` (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`) VALUES
 (168, 'COM:ROOT,PRO:ROOT,MVE:ROOT,ULT:VENDOR', 'store_mode', 0, 0, 'I', 'opened', 15, 'Y');
REPLACE INTO `?:settings_objects` (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`) VALUES
 (198, 'COM:ROOT,PRO:ROOT,MVE:ROOT,ULT:VENDOR', 'store_optimization', 0, 0, 'I', 'dev', 15, 'Y');

UPDATE `?:users` SET `is_root` = 'N' WHERE `company_id` != 0 AND `user_type` = 'V';

DROP TABLE IF EXISTS `?:ult_language_values`;
CREATE TABLE `?:ult_language_values` (
  `lang_code` char(2) NOT NULL DEFAULT 'EN',
  `name` varchar(128) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `company_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`lang_code`,`name`, `company_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS ?:ult_product_descriptions;
CREATE TABLE `?:ult_product_descriptions` (
  `product_id` mediumint(8) unsigned NOT NULL default '0',
  `lang_code` char(2) NOT NULL default 'EN',
  `company_id` int(11) unsigned NOT NULL,
  `product` varchar(255) NOT NULL default '',
  `shortname` varchar(255) NOT NULL default '',
  `short_description` mediumtext NOT NULL,
  `full_description` mediumtext NOT NULL,
  `meta_keywords` varchar(255) NOT NULL default '',
  `meta_description` varchar(255) NOT NULL default '',
  `search_words` text NOT NULL,
  `page_title` varchar(255) NOT NULL default '',
  `age_warning_message` text NOT NULL,
  PRIMARY KEY  (`product_id`,`lang_code`,`company_id`),
  KEY `product_id` (`product_id`),
  KEY `company_id` (`company_id`)
) Engine=MyISAM DEFAULT CHARSET UTF8;


DROP TABLE IF EXISTS ?:ult_product_option_variants;
CREATE TABLE `?:ult_product_option_variants` (
  `variant_id` mediumint(8) unsigned NOT NULL auto_increment,
  `option_id` mediumint(8) unsigned NOT NULL default '0',
  `company_id` int(11) unsigned NOT NULL,
  `modifier` decimal(13,3) NOT NULL default '0.000',
  `modifier_type` char(1) NOT NULL default 'A',
  PRIMARY KEY  (`variant_id`,`company_id`),
  KEY `company_id` (`company_id`),
  KEY (`option_id`,`variant_id`,`company_id`)
) Engine=MyISAM DEFAULT CHARSET UTF8;


DROP TABLE IF EXISTS `?:ult_product_prices`;
CREATE TABLE `?:ult_product_prices` (
  `product_id` mediumint(8) unsigned NOT NULL default '0',
  `price` decimal(12,2) NOT NULL default '0.00',
  `percentage_discount` int(2) unsigned NOT NULL default '0',
  `lower_limit` smallint(5) unsigned NOT NULL default '0',
  `company_id` int(11) unsigned NOT NULL,
  `usergroup_id` mediumint(8) unsigned NOT NULL default '0',
  UNIQUE KEY `usergroup` (`product_id`,`usergroup_id`,`lower_limit`,`company_id`),
  KEY `product_id` (`product_id`),
  KEY `company_id` (`company_id`),
  KEY `lower_limit` (`lower_limit`),
  KEY `usergroup_id` (`usergroup_id`,`product_id`)
) Engine=MyISAM DEFAULT CHARSET UTF8;


/* Change privilege section */
DELETE FROM ?:privileges WHERE privilege = 'view_suppliers';
DELETE FROM ?:privileges WHERE privilege = 'manage_suppliers';

DELETE FROM ?:privilege_descriptions WHERE privilege = 'view_suppliers';
DELETE FROM ?:privilege_descriptions WHERE privilege = 'manage_suppliers';

REPLACE INTO `?:privilege_section_descriptions` (`section_id`, `description`, `lang_code`) VALUES (10, 'Stores', 'EN');

/* Create new privileges for manage/view stores */
INSERT INTO ?:privileges (privilege, is_default) VALUES ('view_stores', 'Y');
INSERT INTO ?:privileges (privilege, is_default) VALUES ('manage_stores', 'Y');

INSERT INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('view_stores', 'View stores', 'EN', '10');
INSERT INTO ?:privilege_descriptions (privilege, description, lang_code, section_id) VALUES ('manage_stores', 'Manage stores', 'EN', '10');

DROP TABLE IF EXISTS `?:settings_vendor_values`;
CREATE TABLE IF NOT EXISTS `?:settings_vendor_values` (
  `object_id` mediumint(8) unsigned NOT NULL, 
  `company_id` int(11) unsigned NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`object_id`,`company_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `?:ult_objects_sharing`;
CREATE TABLE `?:ult_objects_sharing` (
  `share_company_id` int(11) unsigned NOT NULL,
  `share_object_id` varchar(10) NOT NULL default '',
  `share_object_type` varchar(50) NOT NULL default '',
  PRIMARY KEY (`share_object_id`, `share_company_id`, `share_object_type`)
) Engine=MyISAM DEFAULT CHARSET UTF8;
