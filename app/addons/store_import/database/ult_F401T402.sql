INSERT INTO `?:settings_objects` (object_id, edition_type, name, section_id, section_tab_id, type, value, position, is_global, handler) VALUES ('5933', 'ROOT', 'uk_cookies_law', '15', '0', 'C', 'N', '55', 'Y', '') ON DUPLICATE KEY UPDATE `object_id` = `object_id`;
INSERT INTO `?:settings_objects` (object_id, edition_type, name, section_id, section_tab_id, type, value, position, is_global, handler) VALUES ('294', 'ROOT', 'cdn', '0', '0', 'I', '', '10', 'N', '') ON DUPLICATE KEY UPDATE `object_id` = `object_id`;

UPDATE `?:settings_objects` SET edition_type='ROOT,ULT:VENDOR' WHERE name = 'show_menu_mouseover';
UPDATE `?:settings_objects` SET edition_type='ROOT,VENDOR' WHERE name = 'log_type_database';
DELETE FROM `?:settings_objects` WHERE name = 'store_optimization_dev';

ALTER TABLE `?:bm_layouts` ADD `layout_width` enum('fixed','fluid','full_width') NOT NULL DEFAULT 'fixed';
ALTER TABLE `?:bm_layouts` ADD `min_width` int(11) unsigned NOT NULL DEFAULT '760';
ALTER TABLE `?:bm_layouts` ADD `max_width` int(11) unsigned NOT NULL DEFAULT '960';

ALTER TABLE `?:shipping_services` DROP `carrier`;

UPDATE `?:payment_processors` SET processor='Skrill QuickCheckout (ex Moneybookers)', processor_script='skrill.php', admin_template='skrill_qc.tpl' WHERE admin_template='moneybookers_qc.tpl';
UPDATE `?:payment_processors` SET processor='Skrill eWallet (ex Moneybookers)', processor_script='skrill.php', admin_template='skrill_ew.tpl' WHERE admin_template='moneybookers_ew.tpl';

UPDATE `?:shipping_services` SET module = LOWER(module);
UPDATE `?:shipping_services` SET code='Priority Mail Express International' WHERE service_id='43';
UPDATE `?:shipping_service_descriptions` SET description='USPS Priority Mail Express International' WHERE service_id='43';
UPDATE `?:shipping_services` SET code='Priority Mail Express' WHERE service_id='47';
UPDATE `?:shipping_service_descriptions` SET description='USPS Priority Mail Express' WHERE service_id='47';


DELETE FROM `?:payment_processors` WHERE processor_id='17';
DELETE FROM `?:payment_processors` WHERE processor_id='61';
DELETE FROM `?:privileges` WHERE privilege='manage_site_layout';
DELETE FROM `?:usergroup_privileges` WHERE privilege='manage_site_layout';

ALTER TABLE `?:language_values` ADD `original_value` text NOT NULL;
ALTER TABLE `?:settings_descriptions` ADD `original_value` text NOT NULL AFTER value;
UPDATE `?:language_values` SET `lang_code` = LOWER(`lang_code`);

UPDATE `?:settings_variants` SET name = 'use_selected_and_alternative' WHERE object_id = (SELECT object_id FROM ?:settings_objects WHERE name = 'alternative_currency') AND name = 'Y';
UPDATE `?:settings_variants` SET name = 'use_only_selected' WHERE object_id = (SELECT object_id FROM ?:settings_objects WHERE name = 'alternative_currency') AND name = 'N';

UPDATE `?:settings_variants` SET name = 'only_products' WHERE object_id = (SELECT object_id FROM ?:settings_objects WHERE name = 'min_order_amount_type') AND name = 'P';
UPDATE `?:settings_variants` SET name = 'products_with_shippings' WHERE object_id = (SELECT object_id FROM ?:settings_objects WHERE name = 'min_order_amount_type') AND name = 'S';
UPDATE `?:settings_variants` SET name = 'allow_shopping' WHERE object_id = (SELECT object_id FROM ?:settings_objects WHERE name = 'allow_anonymous_shopping') AND name = 'Y';
UPDATE `?:settings_variants` SET name = 'hide_price_and_add_to_cart' WHERE object_id = (SELECT object_id FROM ?:settings_objects WHERE name = 'allow_anonymous_shopping') AND name = 'P';
UPDATE `?:settings_variants` SET name = 'hide_add_to_cart' WHERE object_id = (SELECT object_id FROM ?:settings_objects WHERE name = 'allow_anonymous_shopping') AND name = 'B';

UPDATE `?:settings_objects` SET type = 'U' WHERE name IN ('products_per_page', 'admin_products_per_page', 'admin_elements_per_page', 'admin_pages_per_page', 'admin_orders_per_page', 'orders_per_page', 'elements_per_page');
