INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`) VALUES ('ROOT', 'mailer_smtp_ecrypted_connection', 1, 0, 'S', '', 55, 'N');
INSERT INTO ?:settings_variants (`object_id`, `name`, `position`) VALUES ((SELECT object_id FROM ?:settings_objects WHERE name='mailer_smtp_ecrypted_connection'), 'none', 10);
INSERT INTO ?:settings_variants (`object_id`, `name`, `position`) VALUES ((SELECT object_id FROM ?:settings_objects WHERE name='mailer_smtp_ecrypted_connection'), 'tls', 20);
INSERT INTO ?:settings_variants (`object_id`, `name`, `position`) VALUES ((SELECT object_id FROM ?:settings_objects WHERE name='mailer_smtp_ecrypted_connection'), 'ssl', 30);

INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`) VALUES ((SELECT object_id FROM ?:settings_objects WHERE name='mailer_smtp_ecrypted_connection'), 'O', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'Use Encrypted Connection');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`) VALUES ((SELECT variant_id FROM ?:settings_variants WHERE name = 'none' AND object_id = (SELECT object_id FROM ?:settings_objects WHERE name='mailer_smtp_ecrypted_connection')), 'V', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'None');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`) VALUES ((SELECT variant_id FROM ?:settings_variants WHERE name = 'tls' AND object_id = (SELECT object_id FROM ?:settings_objects WHERE name='mailer_smtp_ecrypted_connection')), 'V', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'TLS');
INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`) VALUES ((SELECT variant_id FROM ?:settings_variants WHERE name = 'ssl' AND object_id = (SELECT object_id FROM ?:settings_objects WHERE name='mailer_smtp_ecrypted_connection')), 'V', (SELECT value FROM ?:settings_objects WHERE name = 'backend_default_language'), 'SSL');

UPDATE ?:payment_processors SET processor_script = "skrill_qc.php" WHERE admin_template = "skrill_qc.tpl";
UPDATE ?:payment_processors SET processor_script = "skrill_ewallet.php" WHERE admin_template = "skrill_ew.tpl";
