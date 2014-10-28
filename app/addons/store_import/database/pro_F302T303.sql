ALTER TABLE `?:payments` ADD `company_id` int(11) unsigned NOT NULL DEFAULT '0';
INSERT INTO `?:payment_processors` (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES ('89', 'Vsevcredit', 'vsevcredit.php', 'cc_outside.tpl', 'vsevcredit.tpl', 'Y', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
INSERT INTO `?:states` (state_id, country_code, code, status) VALUES ('503', 'GB', 'IOS', 'A') ON DUPLICATE KEY UPDATE `state_id` = `state_id`;
DELETE FROM `?:settings_objects` WHERE object_id='92';
DELETE FROM `?:settings_objects` WHERE object_id='142';
DELETE FROM `?:settings_objects` WHERE object_id='181';
DELETE FROM ?:settings_objects WHERE object_id = 276;
DELETE FROM ?:settings_descriptions WHERE object_id = 276 AND object_type = 'O';

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


ALTER TABLE `?:currencies` CHANGE `coefficient` `coefficient` double(12,5) NOT NULL DEFAULT '1.00000';
ALTER TABLE `?:product_features_values` CHANGE `value_int` `value_int` double(12,2) DEFAULT NULL;
ALTER TABLE `?:settings_objects` CHANGE `value` `value` text NOT NULL DEFAULT '';

INSERT INTO `?:state_descriptions` (state_id, lang_code, state) VALUES ('503', 'EN', 'Isles of Scilly') ON DUPLICATE KEY UPDATE `state_id` = `state_id`;
DELETE FROM `?:settings_descriptions` WHERE object_id='181' AND object_type='O';
