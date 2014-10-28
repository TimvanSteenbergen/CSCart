INSERT INTO `?:payment_processors` (processor, processor_script, processor_template, admin_template, callback, type) VALUES ('Alpha Bank', 'alpha_bank.php', 'views/orders/components/payments/cc_outside.tpl', 'alpha_bank.tpl', 'N', 'P') ON DUPLICATE KEY UPDATE `processor_id` = `processor_id`;
DELETE FROM ?:storage_data WHERE data_key IN ('gift_registry_next_check', 'send_feedback');
