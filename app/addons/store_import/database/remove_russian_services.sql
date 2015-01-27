DELETE FROM `?:settings_descriptions` WHERE object_id IN (SELECT object_id FROM `?:settings_objects` WHERE name IN ('russian_post_enabled', 'edost_enabled', 'ems_enabled'));
DELETE FROM `?:settings_objects` WHERE name IN ('russian_post_enabled', 'edost_enabled', 'ems_enabled');

DELETE FROM `?:shipping_rates` WHERE shipping_id IN (SELECT shipping_id FROM `?:shippings` WHERE service_id IN (SELECT service_id FROM `?:shipping_services` WHERE module IN ('edost', 'ems', 'russian_post')));
DELETE FROM `?:shipping_descriptions` WHERE shipping_id IN (SELECT shipping_id FROM `?:shippings` WHERE service_id IN (SELECT service_id FROM `?:shipping_services` WHERE module IN ('edost', 'ems', 'russian_post')));
DELETE FROM `?:shippings` WHERE service_id IN (SELECT service_id FROM `?:shipping_services` WHERE module IN ('edost', 'ems', 'russian_post'));
DELETE FROM `?:shipping_service_descriptions` WHERE service_id IN (SELECT service_id FROM `?:shipping_services` WHERE module IN ('edost', 'ems', 'russian_post'));
DELETE FROM `?:shipping_services` WHERE module IN ('edost', 'ems', 'russian_post');

DELETE FROM `?:payment_descriptions` WHERE payment_id IN (SELECT payment_id FROM `?:payments` WHERE processor_id IN (SELECT processor_id FROM `?:payment_processors` WHERE processor_script IN ('assist.php', 'webmoney.php', 'robokassa.php', 'pay_at_home.php', 'qiwi.php', 'rbk.php', 'vsevcredit.php', 'yes_credit.php', 'kupivkredit.php') OR admin_template = 'sbrf_receipt.tpl'));
DELETE FROM `?:payments` WHERE processor_id IN (SELECT processor_id FROM `?:payment_processors` WHERE processor_script IN ('assist.php', 'webmoney.php', 'robokassa.php', 'pay_at_home.php', 'qiwi.php', 'rbk.php', 'vsevcredit.php', 'yes_credit.php', 'kupivkredit.php') OR admin_template = 'sbrf_receipt.tpl');
DELETE FROM `?:payment_processors` WHERE processor_script IN ('assist.php', 'webmoney.php', 'robokassa.php', 'pay_at_home.php', 'qiwi.php', 'rbk.php', 'vsevcredit.php', 'yes_credit.php', 'kupivkredit.php') OR admin_template = 'sbrf_receipt.tpl';

DELETE FROM `?:addon_descriptions` WHERE addon IN ('kupivkredit', 'exim_1c', 'loginza', 'sbrf', 'edost', 'currencies_sync', 'yandex_market', 'yandex_metrika', 'unisender');
DELETE FROM `?:addons` WHERE addon IN ('kupivkredit', 'exim_1c', 'loginza', 'sbrf', 'edost', 'currencies_sync', 'yandex_market', 'yandex_metrika', 'unisender');

DROP TABLE IF EXISTS ?:exim_1c;
