UPDATE `?:payment_processors` SET processor='eWAY Direct Payment', processor_script='eway_direct.php', processor_template='views/orders/components/payments/cc.tpl', admin_template='eway_direct.tpl', callback='Y', type='P' WHERE processor_id='19';
UPDATE `?:payment_processors` SET processor='eWAY Shared Payment', processor_script='eway_shared.php', processor_template='views/orders/components/payments/cc_outside.tpl', admin_template='eway_shared.tpl', callback='Y', type='P' WHERE processor_id='80';
UPDATE `?:payment_processors` SET processor='eWAY Direct Payment (Rapid API)', processor_script='eway_rapidapi_direct.php', processor_template='views/orders/components/payments/cc.tpl', admin_template='eway_rapidapi.tpl', callback='N', type='P' WHERE processor_id='92';
INSERT INTO `?:payment_processors` (processor, processor_script, processor_template, admin_template, callback, type) VALUES ('eWAY Responsive Shared (Rapid API)', 'eway_rapidapi_rsp.php', 'views/orders/components/payments/cc_outside.tpl', 'eway_rapidapi_rsp.tpl', 'N', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
INSERT INTO `?:payment_processors` (processor, processor_script, processor_template, admin_template, callback, type) VALUES ('FuturePay', 'future_pay.php', 'views/orders/components/payments/cc_outside.tpl', 'future_pay.tpl', 'N', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
DELETE FROM `?:settings_objects` WHERE name IN ('keep_https', 'header_8159');

ALTER TABLE `?:exim_layouts` ADD `options` text NOT NULL;
ALTER TABLE ?:orders ADD profile_id MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE ?:bm_layouts CHANGE `preset_id` `style_id` varchar(64) NOT NULL default "";
