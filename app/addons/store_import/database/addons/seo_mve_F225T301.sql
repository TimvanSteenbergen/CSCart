ALTER TABLE `?:seo_names`
  ADD COLUMN `company_id` int(11) unsigned NULL DEFAULT '0';

ALTER TABLE `?:seo_names` DROP PRIMARY KEY;

ALTER TABLE `?:seo_names` ADD PRIMARY KEY (`object_id`,`type`,`dispatch`,`lang_code`,`company_id`);