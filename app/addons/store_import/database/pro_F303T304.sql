
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

ALTER TABLE `?:settings_objects` CHANGE `value` `value` text NOT NULL;
DROP TABLE `?:sitemap_descriptions`;

UPDATE `?:language_values` SET value='It is strongly recommended that you rename the default <b>admin.php</b> script (check the <a href=\"http://kb.cs-cart.com/adminarea-protection\" target=\"_blank\">Knowledge base</a>) for security reasons.' WHERE lang_code='EN' AND name='warning_insecure_admin_script';
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'application_key', 'Application key') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'browser_upgrade_notice', '<p>We have detected that the browser you are using is not fully supported by the CS-Cart Admin Panel. You can view this site using your current browser but it may not display properly, and you may not be able to fully use all features.</p><br><p>CS-Cart Admin Panel is best viewed using the following browsers:</p><br><ul><li>&ndash; <a href=\"http://windows.microsoft.com/en-US/internet-explorer/products/ie/home\" target=\"_blank\">Internet Explorer 8 and above</a></li><li>&ndash; <a href=\"http://www.mozilla.org/en-US/\" target=\"_blank\">Mozilla Firefox (latest version)</a></li><li>&ndash; <a href=\"https://www.google.com/intl/en/chrome/browser/\" target=\"_blank\">Google Chrome (latest version)</a></li></ul><br><p>Click on one of the links to download the browser of your choice. Once the download has completed, install the browser by running the setup program.</p><br><p>If you cannot upgrade your browser now, you can still access CS-Cart Admin Panel, but you may not be able to fully use all features.<br><br><a href=\"[admin_index]\">Continue</a></p>') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'browser_upgrade_notice_title', 'Browser Upgrade Notice') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'cant_create_file', 'File could not be created') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'deposit_amount', 'Deposit amount') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'email_field_must_be_selected', 'The \'email\' field must be active at least in one of the Billing/Shipping address sections.') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'finance_product_code', 'Finance product code') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'gc_auto_charge', 'Enable auto charge') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'remove_cc_info', 'Remove CC info') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'text_pay4later_notice', '<b>Note</b>: In order to track your Pay4Later orders with the shopping cart software you have to take these steps:<br />
    <br />
    -&nbsp;Log in to Pay4Later BackOffice<br />
    -&nbsp;Click on the <u>\'Settings/Installations\'</u> link in the <u>\'Quick Links\'</u> section.<br />
    -&nbsp;Set <u>\'Return URL (Verified)\'</u> setting to:<br />
    <b>[verified_url]</b><br />
    -&nbsp;Set <u>\'Return URL (Decline)\'</u> setting to:<br />
    <b>[decline_url]</b><br />
    -&nbsp;Set <u>\'Return URL (Refer)\'</u> setting to:<br />
    <b>[refer_url]</b><br />
    -&nbsp;Set <u>\'Return URL (Cancel)\'</u> setting to:<br />
    <b>[cancel_url]</b><br />
    -&nbsp;Set <u>\'CSN URL\'</u> setting to:<br />
    <b>[process_url]</b><br />
    -&nbsp;Click on the <u>\'Save Changes\'</u> button<br />') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'text_payment_have_been_deleted', 'Payment have been deleted successfully.') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'text_payment_have_not_been_deleted', 'Payment cannot be deleted.') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'tt_views_block_manager_update_location_default', 'One location must be picked as default. Its Top and Bottom containers will be used in all locations.') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
