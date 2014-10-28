ALTER TABLE ?:language_values DROP `original_value`;
ALTER TABLE ?:settings_descriptions DROP `original_value`;
DROP TABLE IF EXISTS ?:original_values;
CREATE TABLE ?:original_values (  `msgctxt` varchar(128) NOT NULL DEFAULT '',  `msgid` text,  PRIMARY KEY (`msgctxt`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE ?:settings_objects ADD `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0';
UPDATE ?:settings_objects SET section_id = 15 WHERE name IN ("secure_checkout", "secure_admin", "keep_https", "secure_auth") AND section_id = 2;
UPDATE ?:settings_objects SET name = "save_selected_view" WHERE name = "save_selected_layout";
UPDATE ?:settings_objects SET name = "default_products_view" WHERE name = "default_products_layout";
UPDATE ?:settings_objects SET name = "default_products_view_templates" WHERE name = "default_products_layout_templates";
UPDATE ?:settings_objects SET name = "default_product_details_view" WHERE name = "default_product_details_layout";
