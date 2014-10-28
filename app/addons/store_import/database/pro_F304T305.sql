UPDATE `?:language_values` SET value='Customer country (shipping)' WHERE lang_code='EN' AND name='promotion_cond_country';
UPDATE `?:language_values` SET value='Customer state (shipping)' WHERE lang_code='EN' AND name='promotion_cond_state';
UPDATE `?:language_values` SET value='Customer zip/postal code (shipping)' WHERE lang_code='EN' AND name='promotion_cond_zip_postal_code';
INSERT INTO `?:language_values` (lang_code, name, value) VALUES ('EN', 'control_summ_wrong', 'Wrong control summ') ON DUPLICATE KEY UPDATE `lang_code` = `lang_code`;
