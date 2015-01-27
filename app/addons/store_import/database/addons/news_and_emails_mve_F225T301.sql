ALTER TABLE `?:news`
  ADD COLUMN `company_id` int(11) unsigned NULL DEFAULT '0';

ALTER TABLE `?:user_mailing_lists`
  MODIFY COLUMN `format` tinyint(3) unsigned NOT NULL DEFAULT 1;
