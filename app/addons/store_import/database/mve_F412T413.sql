DELETE FROM `?:payment_descriptions` WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM `?:payment_processors` WHERE processor_script = 'google_checkout.php'));
DELETE FROM `?:payments` WHERE processor_id IN (SELECT processor_id FROM `?:payment_processors` WHERE processor_script = 'google_checkout.php');
DELETE FROM `?:payment_processors` WHERE processor_script='google_checkout.php';
DELETE FROM `?:settings_objects` WHERE name='redirect_to_cart';

INSERT INTO `?:settings_objects` (edition_type, name, section_id, section_tab_id, type, value, position, is_global, handler) VALUES ('ROOT,ULT:VENDOR', 'keep_https', '2', '0', 'C', 'N', '5', 'Y', '');
INSERT INTO `?:settings_descriptions` (object_id, object_type, lang_code, value, original_value, tooltip) VALUES ((SELECT object_id FROM ?:settings_objects WHERE name = 'keep_https'), 'O', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'Keep HTTPS connection once a secure page is visited', 'Keep HTTPS connection once a secure page is visited', '');
