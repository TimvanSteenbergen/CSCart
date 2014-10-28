UPDATE `?:attachment_descriptions` SET `lang_code` = LOWER(`lang_code`);
UPDATE `?:attachment_descriptions` SET `lang_code` = 'sl' WHERE `lang_code` = 'si';